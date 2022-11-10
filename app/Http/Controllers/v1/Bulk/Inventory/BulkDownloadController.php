<?php

namespace App\Http\Controllers\v1\Bulk\Inventory;

use App\Http\Controllers\v1\Jobs\MonitoredJobsController;
use App\Http\Requests\Bulk\Inventory\CreateBulkDownloadRequest;
use App\Jobs\Bulk\Inventory\ProcessDownloadJob;
use App\Services\Export\Inventory\Bulk\BulkDownloadJobServiceInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Bulk\Inventory\BulkDownload;
use App\Models\Bulk\Inventory\BulkDownloadPayload;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use Dingo\Api\Http\Request;

class BulkDownloadController extends MonitoredJobsController
{
    /** @var BulkDownloadJobServiceInterface */
    private $service;

    public function __construct(
        MonitoredJobRepositoryInterface $jobsRepository,
        BulkDownloadJobServiceInterface $service
    )
    {
        parent::__construct($jobsRepository);

        $this->middleware('setDealerIdOnRequest')->only(['index', 'status', 'create']);

        $this->service = $service;
    }

    /**
     * Will enqueue a job to assembly the file, it will response with a job UUID, then the client should
     * periodically ask for the job status, when it is `completed` the client should handle the download process.
     *
     * If `wait` is provided, it will run the file assembly immediately,
     * then it will download it (no need for separate api call)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|StreamedResponse
     */
    public function create(Request $request)
    {
        $request = new CreateBulkDownloadRequest($request->all());

        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $output = $request->output();

        $payload = BulkDownloadPayload::from([
                'filename' => str_replace('.', '-', uniqid('inventory-' . date('Ymd'), true)) . '.' . $output,
                'output' => $output,
                'orientation' => $request->orientation(),
                'filters' => $request->filters()
            ]
        );

        $job = $this->service
            ->setup($request->dealer_id, $payload, $request->token)
            ->withQueueableJob(static function (BulkDownload $bulk): ProcessDownloadJob {
                return new ProcessDownloadJob($bulk->token);
            });

        if ($request->wait()) {
            $this->service->dispatchNow($job);

            return $this->readStream($job);
        }

        $this->service->dispatch($job);

        return response()->json(['token' => $job->token], 202);

    }

    /**
     * @param BulkDownload $job
     * @return StreamedResponse
     */
    protected function readStream($job): StreamedResponse
    {
        $job = BulkDownload::fromMonitoredJob($job);

        return response()->streamDownload(function () use ($job) {
            fpassthru($this->service->handler($job->payload->output)->readStream($job));
        }, $job->payload->filename);
    }
}
