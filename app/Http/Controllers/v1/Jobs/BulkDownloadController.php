<?php

namespace App\Http\Controllers\v1\Jobs;

use App\Exceptions\Common\BusyJobException;
use App\Http\Controllers\Controller;
use App\Models\Bulk\Parts\BulkDownloadPayload;
use App\Repositories\Common\MonitoredJobRepository;
use App\Services\Export\Parts\BulkDownloadMonitoredJobServiceInterface;
use Dingo\Api\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkDownloadController extends Controller
{
    /**
     * @var MonitoredJobRepository
     */
    private $repository;

    /**
     * @var BulkDownloadMonitoredJobServiceInterface
     */
    private $service;

    public function __construct(MonitoredJobRepository $repository, BulkDownloadMonitoredJobServiceInterface $service)
    {
        $this->middleware('setDealerIdOnRequest')->only(['create']);
        $this->repository = $repository;
        $this->service = $service;
    }

    /**
     * Create a bulk csv file download request
     *
     * @param Request $request
     * @return JsonResponse|StreamedResponse
     * @throws BusyJobException when there is currently other job working
     *
     * @OA\Post(
     *     path="/api/parts/bulk/download",
     * )
     */
    public function create(Request $request)
    {
        $dealerId = $request->input('dealer_id');
        $token = $request->input('token');

        $payload = BulkDownloadPayload::from(['export_file' => 'parts-' . date('Ymd') . '-' . $token . '.csv']);

        $model= $this->service->setup($dealerId, $payload, $token);

        $this->service->dispatch($model);

        // if requested, wait for file assembly then download it now. no need for separate api call
        if ($request->input('wait')) {
            return $this->waitForFile($model->token);
        }

        return response()->json(['token' => $model->token], 202);
    }

    /**
     * Download the completed CSV file created from the request
     *
     * @param string $token The token returned by the create service
     * @return JsonResponse|StreamedResponse
     *
     * @OA\Get(
     *     path="/api/parts/bulk/file/{token}",
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true
     *     )
     * )
     */
    public function read(string $token)
    {
        $download = $this->repository->findByToken($token);

        if ($download->isPending()) {
            return response()->json(['message' => 'Still processing', 'progress' => $download->progress,], 202);
        }

        if ($download->isFailed()) {
            return response()->json([
                'message' => 'This file could not be completed. Please request a new file.',
            ], 500);
        }

        if ($download->isCompleted()) {
            return response()->streamDownload(static function() use ($download) {
                fpassthru(Storage::disk('partsCsvExports')->readStream($download->payload->export_file));
            }, $download->export_file);
        }

        return response()->json(['message' => 'Error: unknown status',], 500);
    }

    /**
     * Check status of CSV file building
     *
     * @param string $token The token returned by the create service
     * @return JsonResponse|StreamedResponse
     *
     * @OA\Get(
     *     path="/api/parts/bulk/status/{token}",
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true
     *     )
     * )
     */
    public function status(string $token)
    {
        $download = $this->repository->findByToken($token);

        if ($download->isPending()) {
            return response()->json(['message' => 'Still processing', 'progress' => $download->progress]);
        }

        if ($download->isCompleted()) {
            return response()->json(['message' => 'Completed', 'progress' => $download->progress]);
        }

        if ($download->isFailed()) {
            return response()->json([
                'message' => 'This file could not be completed. Please request a new file.',
            ], 500);
        }

        return response()->json(['message' => 'Error: unknown status'], 500);
    }

    /**
     * Wait for file assembly then return the file
     *
     * @param string $token
     * @return JsonResponse|StreamedResponse
     */
    private function waitForFile(string $token)
    {
        $timeStart = time();

        // read loop
        while (true) {
            $result = $this->read($token);

            if (time() - $timeStart > 180) { // 3 minutes
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
}
