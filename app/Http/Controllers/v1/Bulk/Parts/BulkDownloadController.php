<?php

namespace App\Http\Controllers\v1\Bulk\Parts;

use App\Exceptions\Common\BusyJobException;
use App\Http\Controllers\v1\Jobs\MonitoredJobsController;
use App\Http\Requests\Bulk\Parts\CreateBulkDownloadRequest;
use App\Http\Requests\Jobs\GetMonitoredJobsRequest;
use App\Jobs\Bulk\Parts\CsvExportJob;
use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Bulk\Parts\BulkDownloadPayload;
use App\Repositories\Bulk\Parts\BulkDownloadRepositoryInterface;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Services\Export\Parts\BulkDownloadMonitoredJobServiceInterface;
use Dingo\Api\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkDownloadController extends MonitoredJobsController
{
    /**
     * seconds that it will wait for the stream
     */
    public const DEFAULT_READ_TIMEOUT = 180; // 3 minutes

    protected $failedMessage = 'This file could not be completed. Please request a new file.';

    /**
     * @var BulkDownloadRepositoryInterface
     */
    private $bulkRepository;

    /**
     * @var BulkDownloadMonitoredJobServiceInterface
     */
    private $service;

    /**
     * @var int seconds that it will wait for the stream
     */
    private $readTimeOut;

    public function __construct(
        BulkDownloadRepositoryInterface $bulkRepository,
        MonitoredJobRepositoryInterface $jobsRepository,
        BulkDownloadMonitoredJobServiceInterface $service
    )
    {
        parent::__construct($jobsRepository);

        $this->middleware('setDealerIdOnRequest')->only(['index', 'status', 'create']);

        $this->bulkRepository = $bulkRepository;
        $this->service = $service;
        $this->readTimeOut = self::DEFAULT_READ_TIMEOUT;
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
    public function create(Request $request)
    {
        $request = new CreateBulkDownloadRequest($request->all());

        if ($request->validate()) {
            $token = $request->input('token');

            $payload = BulkDownloadPayload::from(['export_file' => 'parts-' . date('Ymd') . '-' . $token . '.csv']);

            $model = $this->service
                ->setup($request->input('dealer_id'), $payload, $token)
                ->withQueueableJob(static function (BulkDownload $job): CsvExportJob {
                    return new CsvExportJob($job);
                });

            $this->service->dispatch($model);

            // if requested, wait for file assembly then download it now. no need for separate api call
            if ($request->input('wait')) {
                return $this->waitForFile($model->token, $request);
            }

            return response()->json(['token' => $model->token], 202);
        }

        $this->response->errorBadRequest();
    }

    /**
     * Download the completed CSV file created from the request
     *
     * @param string $token The token returned by the create service
     * @param Request $request
     * @return JsonResponse|StreamedResponse|void
     *
     * @OA\Get(
     *     path="/api/parts/bulk/file/{token}",
     *     description="Download the completed CSV file created from the request",
     *     tags={"BulkDownloadParts"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true
     *     )
     * )
     */
    public function read(string $token, Request $request)
    {
        $request = new GetMonitoredJobsRequest(array_merge($request->all(), ['token' => $token]));

        if ($request->validate()) {
            $download = $this->bulkRepository->findByToken($token);

            if ($download->isPending()) {
                return response()->json(['message' => 'It is pending', 'progress' => $download->progress], 202);
            }

            if ($download->isProcessing()) {
                return response()->json(['message' => 'Still processing', 'progress' => $download->progress]);
            }

            if ($download->isFailed()) {
                return response()->json([
                    'message' => 'This file could not be completed. Please request a new file.',
                ], 500);
            }

            return response()->streamDownload(static function () use ($download) {
                fpassthru(Storage::disk('partsCsvExports')->readStream($download->payload->export_file));
            }, $download->export_file);
        }

        $this->response->errorBadRequest();
    }

    /**
     * Wait for file assembly then return the file
     *
     * @param string $token
     * @param Request $request
     * @return JsonResponse|StreamedResponse
     */
    private function waitForFile(string $token, Request $request)
    {
        $timeStart = time();

        // read loop
        while (true) {
            $result = $this->read($token, $request);

            if (time() - $timeStart > $this->readTimeOut) {
                break;
            }

            if ($result->getStatusCode() === 202) {
                sleep(10);
                continue;
            }

            return $result;
        }

        return response()->json(['message' => 'Error: unknown status'], 500);
    }

    public function setReadTimeOut(int $readTimeOut): void
    {
        $this->readTimeOut = $readTimeOut;
    }

    /**
     * @OA\Get(
     *     path="/api/parts/bulk/status/{token}",
     *     description="Check status of the process",
     *     tags={"BulkDownloadParts"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true
     *     )
     * )
     */
}
