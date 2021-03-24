<?php

namespace App\Http\Controllers\v1\Bulk\Parts;

use App\Http\Controllers\Controller;
use App\Jobs\Bulk\Parts\CsvExportJob;
use App\Models\Bulk\Parts\BulkDownload;
use App\Repositories\Bulk\BulkDownloadRepositoryInterface as BulkDownloadRepository;
use App\Services\Export\Parts\CsvExportService;
use App\Services\Export\FilesystemCsvExporter;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Storage;

class BulkDownloadController extends Controller
{

    public function __construct()
    {
        $this->middleware('setDealerIdOnRequest')->only(['create']);
    }

    /**
     * Create a bulk csv file download request
     *
     * @param Request $request
     * @param BulkDownloadRepository $repository
     * @return \Illuminate\Http\JsonResponse
     * @throws \League\Csv\Exception
     *
     * @OA\Post(
     *     path="/api/parts/bulk/download",
     * )
     */
    public function create(Request $request, BulkDownloadRepository $repository)
    {
        $dealerId = $request->input('dealer_id');

        // create a new download token (can be anything)
        $token = uniqid();

        // create a download job
        $download = $repository->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'export_file' => 'parts-'. date('Ymd') . '-' . $token. '.csv'] // parts-20200305-$token.csv
        );
        $download->save();

        // create a queueable job
        $job = new CsvExportJob(
            app(CsvExportService::class),
            $download,
            new FilesystemCsvExporter(
                Storage::disk('partsCsvExports'),
                $download->export_file
            )
        );

        // dispatch job to queue
        $this->dispatch($job->onQueue('parts-export-new'));

        // if requested, wait for file assembly then download it now. no need for separate api call
        if ($request->input('wait')) {
            return $this->waitForFile($download->token, $repository);
        }

        return response()->json([
            'token' => $download->token
        ], 202);
    }

    /**
     * Download the completed CSV file created from the request
     *
     * @param string $token The token returned by the create service
     * @param Request $request
     * @param BulkDownloadRepository $repository
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
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
    public function read($token, BulkDownloadRepository $repository)
    {
        $download = $repository->findByToken($token);

        if ($download->status === BulkDownload::STATUS_NEW ||
            $download->status === BulkDownload::STATUS_PROCESSING
        ) {
            return response()->json([
                'message' => 'Still processing',
                'progress' => $download->progress,
            ], 202);
        }

        if ($download->status === BulkDownload::STATUS_ERROR) {
            return response()->json([
                'message' => 'This file could not be completed. Please request a new file.',
            ], 500);
        }

        if ($download->status === BulkDownload::STATUS_COMPLETED) {
            return response()->streamDownload(function() use ($download) {
                fpassthru(Storage::disk('partsCsvExports')->readStream($download->export_file));
            }, $download->export_file);
        }

        return response()->json([
            'message' => 'Error: unknown status',
        ], 500);
    }

    /**
     * Check status of CSV file building
     *
     * @param string $token The token returned by the create service
     * @param Request $request
     * @param BulkDownloadRepository $repository
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
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
    public function status($token, BulkDownloadRepository $repository)
    {
        $download = $repository->findByToken($token);

        if ($download->status === BulkDownload::STATUS_NEW ||
            $download->status === BulkDownload::STATUS_PROCESSING
        ) {
            return response()->json([
                'message' => 'Still processing',
                'progress' => $download->progress,
            ], 200);
        }

        if ($download->status === BulkDownload::STATUS_COMPLETED
        ) {
            return response()->json([
                'message' => 'Completed',
                'progress' => $download->progress,
            ], 200);
        }

        if ($download->status === BulkDownload::STATUS_ERROR) {
            return response()->json([
                'message' => 'This file could not be completed. Please request a new file.',
            ], 500);
        }

        return response()->json([
            'message' => 'Error: unknown status',
        ], 500);
    }

    /**
     * Wait for file assembly then return the file
     * @param $token
     * @param BulkDownloadRepository $repository
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function waitForFile($token, BulkDownloadRepository $repository)
    {
        $timeStart = time();

        while (true) {
            $result = $this->read($token, $repository);

            if (time() - $timeStart > 180) { // 3 minutes
                break;
            }

            if ($result->getStatusCode() == 202) {
                sleep(10);
                continue;
            }

            return $result;
        }

        return response()->json([
            'message' => 'Error: unknown status',
        ], 500);
    }

}
