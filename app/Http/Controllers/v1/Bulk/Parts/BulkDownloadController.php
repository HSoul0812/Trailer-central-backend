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
     *     @OA\Parameter(
     *         name="dealerId",
     *         in="query",
     *         description="Dealer ID to get parts for",
     *         required=true,
     *     )
     * )
     */
    public function create(Request $request, BulkDownloadRepository $repository)
    {
        $dealerId = $request->input('dealerId');

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
        $this->dispatch($job);

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
     * @return mixed
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

    }

}
