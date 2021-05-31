<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Bulk\Parts;

use App\Http\Controllers\v1\Jobs\MonitoredJobsController;
use App\Http\Requests\Bulk\Parts\CreateBulkReportRequest;
use App\Http\Requests\Dms\ServiceOrder\GetServiceReportRequest;
use App\Repositories\Dms\StockRepositoryInterface;
use App\Transformers\Bulk\Stock\StockReportTransformer;
use Dingo\Api\Http\Response;
use League\Fractal\Resource\Collection;
use League\Fractal\Manager;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Jobs\Bulk\Parts\FinancialReportExportJob;
use App\Jobs\Dms\ServiceOrder\ServiceTechnicianExportJob;
use App\Models\Common\MonitoredJob;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Models\Bulk\Parts\BulkReport;
use App\Models\Bulk\Parts\BulkReportPayload;
use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;
use App\Services\Export\Parts\BulkReportJobServiceInterface;
use App\Services\Dms\ServiceOrder\BulkCsvTechnicianReportServiceInterface;
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
     * @var BulkCsvTechnicianReportServiceInterface
     */
    protected $technicianService;

    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(BulkReportRepositoryInterface $repository,
                                MonitoredJobRepositoryInterface $jobsRepository,
                                StockRepositoryInterface $stockRepository,
                                BulkReportJobServiceInterface $service,
                                BulkCsvTechnicianReportServiceInterface $technicianService,
                                Manager $fractal)
    {
        parent::__construct($jobsRepository);

        $this->middleware('setDealerIdOnRequest')->only(['financials', 'financialsExport', 'serviceReportExport']);

        $this->repository = $repository;
        $this->stockRepository = $stockRepository;
        $this->service = $service;
        $this->technicianService = $technicianService;
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
     *         description="The dealer ID.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         description="Search by sku/stock, title and bin_name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type_of_stock",
     *         description="Type of data, ot could be inventories, parts and mixed",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *         description="The dealer ID.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         description="The token for the job.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         description="Search by sku/stock, title and bin_name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type_of_stock",
     *         description="Type of data, ot could be inventories, parts and mixed",
     *         required=false,
     *         @OA\Schema(type="string")
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
     * Create a bulk csv file download request
     *
     * @param Request $request
     * @return JsonResponse|void when there is a bad request it will throw an HttpException and request life cycle ends
     * @throws Exception
     *
     * @OA\Post(
     *     path="/api/dms/reports/service-technician-sales-export",
     *     description="Create a bulk csv file download request",
     *     tags={"BulkReportParts"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         description="The dealer ID.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         description="The token for the job.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="completed_on_type",
     *         description="The completed status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="repair_order_status",
     *         description="The status of the service",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="repair_order_type",
     *         description="The order type",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="technician_id",
     *         description="The ID of the technician",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         description="The initial date for the range of the service date",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         description="The ending date for the range of the service date",
     *         required=false,
     *         @OA\Schema(type="string")
     *     )
     * )
     */
    public function serviceReportExport(Request $request): JsonResponse
    {
        $request = new GetServiceReportRequest($request->all());

        if ($request->validate()) {

            $payload = BulkReportPayload::from([
                'filename' => str_replace('.', '-', uniqid('services-technicians-' . date('Ymd'), true)) . '.csv',
                'filters' => $request->all()
            ]);

            $model = $this->technicianService
                ->setup($request->get('dealer_id'), $payload, $request->get('token'))
                ->withQueueableJob(static function (MonitoredJob $job): ServiceTechnicianExportJob {
                    return new ServiceTechnicianExportJob($job->token);
                });

            $this->technicianService->dispatch($model);

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

    /**
     * @OA\Get(
     *     path="/api/reports/read",
     *     description="Download the completed file created from the request",
     *     tags={"BulkReportParts"},
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="The job token",
     *         required=true
     *     )
     * )
     */
}
