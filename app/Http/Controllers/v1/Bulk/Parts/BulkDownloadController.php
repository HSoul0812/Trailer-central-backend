<?php

namespace App\Http\Controllers\v1\Bulk\Parts;

use App\Exceptions\Common\BusyJobException;
use App\Http\Controllers\v1\Jobs\MonitoredJobsController;
use App\Http\Requests\Bulk\Parts\CreateBulkDownloadRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Jobs\Bulk\Parts\CsvExportJob;
use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Bulk\Parts\BulkDownloadPayload;
use App\Models\Common\MonitoredJob;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Services\Export\Parts\BulkDownloadMonitoredJobServiceInterface;
use Dingo\Api\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class BulkDownloadController extends MonitoredJobsController
{
    protected $failedMessage = 'This file could not be completed. Please request a new file.';

    /**
     * @var BulkDownloadMonitoredJobServiceInterface
     */
    private $service;

    public function __construct(
        MonitoredJobRepositoryInterface $jobsRepository,
        BulkDownloadMonitoredJobServiceInterface $service
    )
    {
        parent::__construct($jobsRepository);

        $this->middleware('setDealerIdOnRequest')->only(['index', 'status', 'create']);

        $this->service = $service;
    }

    /**
     * Create a bulk csv file download request
     *
     * @param Request $request
     * @return JsonResponse|StreamedResponse|void
     * @throws BusyJobException when there is currently other job working
     *
     * @OA\Post(
     *     path="/api/parts/bulk/download",
     *     description="Create a bulk csv file download request",
     *     tags={"BulkDownloadParts"},
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
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request."
     *     )
     * )
     */
    public function create(Request $request)
    {
        $request = new CreateBulkDownloadRequest($request->all());

        if ($request->validate()) {
            $token = $request->input('token');

            $payload = BulkDownloadPayload::from([
                'export_file' => str_replace('.', '-', uniqid('parts-' . date('Ymd'), true)) . '.csv']
            );
            $model = $this->service
                ->setup($request->input('dealer_id'), $payload, $token)
                ->withQueueableJob(static function (BulkDownload $job): CsvExportJob {
                    return new CsvExportJob($job);
                });

            // if requested, wait for file assembly then download it now. no need for separate api call
            if ($request->input('wait')) {
                $this->service->dispatchNow($model);

                return $this->readStream($model);
            }

            $this->service->dispatch($model);

            return response()->json(['token' => $model->token], 202);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param MonitoredJob|BulkDownload $job
     * @return StreamedResponse
     */
    protected function readStream($job): StreamedResponse
    {
        $payload = BulkDownloadPayload::from($job instanceof BulkDownload ? $job->payload->asArray() : $job->payload);

        return response()->streamDownload(static function () use ($payload) {
            fpassthru(Storage::disk('partsCsvExports')->readStream($payload->export_file));
        }, $payload->export_file);
    }

    /**
     *  @OA\Get(
     *     path="/api/parts/bulk/file/{token}",
     *     description="Download the completed CSV file created from the request",
     *     tags={"BulkDownloadParts"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @OA\Get(
     *     path="/api/parts/bulk/status/{token}",
     *     description="Check status of the process",
     *     tags={"BulkDownloadParts"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
}
