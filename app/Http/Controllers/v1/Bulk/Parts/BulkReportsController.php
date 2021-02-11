<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Bulk\Parts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bulk\Parts\CreateBulkReportRequest;
use App\Http\Requests\Jobs\ReadMonitoredJobsRequest;
use App\Jobs\Bulk\Parts\FinancialReportExportJob;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Bulk\Parts\BulkReport;
use App\Models\Bulk\Parts\BulkReportPayload;
use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;
use App\Services\Export\Parts\BulkReportJobServiceInterface;
use Dingo\Api\Routing\Helpers as ControllerHelpers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Dingo\Api\Http\Request;
use Exception;

class BulkReportsController extends Controller
{
    use ControllerHelpers;

    /**
     * @var BulkReportRepositoryInterface
     */
    protected $repository;

    /**
     * @var BulkReportJobServiceInterface
     */
    protected $service;

    /**
     * @param BulkReportRepositoryInterface $repository
     * @param BulkReportJobServiceInterface $service
     */
    public function __construct(BulkReportRepositoryInterface $repository, BulkReportJobServiceInterface $service)
    {
        $this->middleware('setDealerIdOnRequest')->only(['financials']);

        $this->repository = $repository;
        $this->service = $service;
    }

    /**
     * Create a bulk pdf file download request
     *
     * @param Request $request
     * @return JsonResponse|void
     * @throws Exception
     *
     * @OA\Post(
     *     path="/api/reports/financials-parts",
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
     * )
     */
    public function financials(Request $request): JsonResponse
    {
        $request = new CreateBulkReportRequest($request->all());

        if ($request->validate()) {

            $payload = BulkReportPayload::from([
                'filename' => str_replace('.', '-', uniqid('financials-parts-' . date('Ymd'), true)) . '.pdf',
                'type' => BulkReport::TYPE_FINANCIALS
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
     * Download the completed file created from the request
     *
     * @param Request $request
     * @return JsonResponse|StreamedResponse|void
     *
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
    public function read(Request $request)
    {
        $request = new ReadMonitoredJobsRequest($request->all());

        if ($request->validate()) {
            $job = $this->repository->findByToken($request->get('token'));

            if ($job === null) {
                $this->response->errorNotFound('Job not found');
            }

            if ($job->isPending()) {
                return response()->json(['message' => 'It is pending', 'progress' => $job->progress], 202);
            }

            if ($job->isProcessing()) {
                return response()->json(['message' => 'Still processing', 'progress' => $job->progress]);
            }

            if ($job->isFailed()) {
                return response()->json([
                    'message' => 'This file could not be completed. Please request a new file.',
                ], 500);
            }

            return response()->streamDownload(static function () use ($job) {
                fpassthru(Storage::disk('tmp')->readStream($job->payload->filename));
            }, $job->payload->filename);
        }

        $this->response->errorBadRequest();
    }
}
