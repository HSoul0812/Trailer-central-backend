<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Pos;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Pos\Sales\Reports\PostCustomSalesReportRequest;
use App\Repositories\Pos\SalesReportRepositoryInterface;
use App\Services\Pos\CustomSalesReportExporterServiceInterface;
use App\Transformers\Pos\Sales\Reports\CustomSalesReportTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Storage;
use League\Csv\CannotInsertRecord;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Controller for Sales Reports API
 */
class SalesReportController extends RestfulControllerV2
{
    /**
     * @var SalesReportRepositoryInterface
     */
    private $salesRepository;

    /**
     * @var CustomSalesReportExporterServiceInterface
     */
    private $salesReportService;

    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        SalesReportRepositoryInterface $salesRepository,
        CustomSalesReportExporterServiceInterface $salesReportService,
        Manager $fractal)
    {
        $this->salesRepository = $salesRepository;
        $this->salesReportService = $salesReportService;
        $this->middleware('setDealerIdOnRequest')->only(['customReport', 'exportCustomReport']);
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    /**
     * Provides the Custom Sales transactions
     *
     * @param Request $request
     *
     * @return Response|void when there is a bad request it will throw an HttpException and request life cycle ends
     * @OA\Post(
     *     path="/reports/custom-sales",
     *     @OA\Response(
     *         response="200",
     *         description="Returns a floorplan payment created",
     *         @OA\JsonContent()
     *     )
     * )
     *
     * @throws NoObjectIdValueSetException
     * @throws HttpException when there is a bad request
     */
    public function customReport(Request $request): Response
    {
        $request = new PostCustomSalesReportRequest($request->all());

        if ($request->validate()) {
            $data = new Collection(
                $this->salesRepository->customReport($request->all()),
                new CustomSalesReportTransformer(),
                'data'
            );

            return $this->response->array($this->fractal->createData($data)->toArray());
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param Request $request
     * @return StreamedResponse|void when there is a bad request it will throw an HttpException and request life cycle ends
     * @throws NoObjectIdValueSetException
     * @throws HttpException when there is a bad request
     * @throws CannotInsertRecord when cannot insert a line into the csv output file
     */
    public function exportCustomReport(Request $request): StreamedResponse
    {
        $request = new PostCustomSalesReportRequest($request->all());

        if ($request->validate()) {
            $filename = $this->salesReportService->withFileSystem(Storage::disk('tmp'))->run($request->all());

            return response()->streamDownload(
                static function () use ($filename) {
                    fpassthru(Storage::disk('tmp')->readStream($filename));
                },
                $filename,
                ['Access-Control-Expose-Headers' => 'Content-Disposition']
            );
        }

        $this->response->errorBadRequest();
    }
}
