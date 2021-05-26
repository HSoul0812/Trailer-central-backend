<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Bulk\Parts;

use App\Http\Controllers\v1\Jobs\MonitoredJobsController;
use App\Http\Requests\Bulk\Parts\CreateBulkReportRequest;
use App\Repositories\Dms\StockRepositoryInterface;
use App\Transformers\Bulk\Stock\StockReportTransformer;
use Dingo\Api\Http\Response;
use League\Fractal\Resource\Collection;
use League\Fractal\Manager;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Jobs\Bulk\Parts\FinancialReportExportJob;
use App\Models\Common\MonitoredJob;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Models\Bulk\Parts\BulkReport;
use App\Models\Bulk\Parts\BulkReportPayload;
use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;
use App\Services\Export\Parts\BulkReportJobServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Dingo\Api\Http\Request;
use Exception;

class BulkReportsController extends MonitoredJobsController
{
    /**
     * @var BulkReportRepositoryInterface
     */
    protected $repository;

    /**
     * @var StockRepositoryInterface
     */
    protected $stockRepository;

    /**
     * @var BulkReportJobServiceInterface
     */
    protected $service;

    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(BulkReportRepositoryInterface $repository,
                                MonitoredJobRepositoryInterface $jobsRepository,
                                StockRepositoryInterface $stockRepository,
                                BulkReportJobServiceInterface $service,
                                Manager $fractal)
    {
        parent::__construct($jobsRepository);

        $this->middleware('setDealerIdOnRequest')->only(['financials', 'financialsExport']);

        $this->repository = $repository;
        $this->stockRepository = $stockRepository;
        $this->service = $service;
        $this->fractal = $fractal;
    }

    /**
     * Create a bulk pdf file download request
     *
     * @param Request $request
     * @return Response|void when there is a bad request it will throw an HttpException and request life cycle ends
     * @throws Exception
     *
     * @OA\Post(
     *     path="/api/reports/financials-stock",
     *     description="Retrieve all data for the stock report",
     *     tags={"BulkReportParts"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="path",
     *         description="The dealer ID.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="path",
     *         description="Search by sku/stock, title and bin_name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type_of_stock",
     *         in="path",
     *         description="Type of data, ot could be inventories, parts and mixed",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function financials(Request $request): Response
    {
        $request = new CreateBulkReportRequest($request->all());

        if ($request->validate()) {

            $data = new Collection(
                $this->stockRepository->financialReport($request->all()),
                new StockReportTransformer(),
                'data'
            );

            return $this->response->array($this->fractal->createData($data)->toArray());
        }

        $this->response->errorBadRequest();
    }

    /**
     * Create a bulk pdf file download request
     *
     * @param Request $request
     * @return JsonResponse|void when there is a bad request it will throw an HttpException and request life cycle ends
     * @throws Exception
     *
     * @OA\Post(
     *     path="/api/reports/financials-stock-export",
     *     description="Create a bulk pdf file download request",
     *     tags={"BulkReportParts"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="path",
     *         description="The dealer ID.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         description="The token for the job.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="path",
     *         description="Search by sku/stock, title and bin_name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type_of_stock",
     *         in="path",
     *         description="Type of data, ot could be inventories, parts and mixed",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function financialsExport(Request $request): JsonResponse
    {
        $request = new CreateBulkReportRequest($request->all());

        if ($request->validate()) {

            $payload = BulkReportPayload::from([
                'filename' => str_replace('.', '-', uniqid('financials-parts-' . date('Ymd'), true)) . '.pdf',
                'type' => BulkReport::TYPE_FINANCIALS,
                'filters' => $request->all()
            ]);

            $model = $this->service
                ->setup($request->get('dealer_id'), $payload, $request->get('token'))
                ->withQueueableJob(static function (BulkReport $job): FinancialReportExportJob {
                    return new FinancialReportExportJob($job->token);
                });

            $this->service->dispatch($model);

            return response()->json(['token' => $model->token], 202);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param MonitoredJob $job
     * @return StreamedResponse
     */
    protected function readStream($job): StreamedResponse
    {
        $payload = BulkReportPayload::from($job->payload);

        return response()->streamDownload(static function () use ($payload) {
            fpassthru(Storage::disk('tmp')->readStream($payload->filename));
        }, $payload->filename);
    }
}
