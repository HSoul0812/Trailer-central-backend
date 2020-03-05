<?php


namespace App\Http\Controllers\v1\Bulk\Parts;


use App\Http\Controllers\Controller;
use App\Jobs\Bulk\Parts\CsvExportJob;
use App\Repositories\Bulk\BulkDownloadRepositoryInterface as BulkDownloadRepository;
use App\Services\Export\Parts\CsvExportService;
use App\Services\Export\Parts\FilesystemCsvExporter;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Storage;

class BulkDownloadController extends Controller
{
    /**
     * Create a bulk csv file download request
     * @param Request $request
     * @param BulkDownloadRepository $repository
     * @return \Illuminate\Http\JsonResponse
     * @throws \League\Csv\Exception
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
            'export_file' => 'parts-'. date('Ymd') . '-' . uniqid(). '.csv'] // parts-20200305-$token.csv
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
}
