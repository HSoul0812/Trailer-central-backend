<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\Common\UndefinedReportTypeException;
use App\Models\Bulk\Parts\BulkReport;
use App\Models\Bulk\Parts\BulkReportPayload;
use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Repositories\Parts\BinRepositoryInterface;
use App\Services\Common\AbstractMonitoredJobService;
use App\Services\Export\FilesystemPdfExporter;
use App\Services\Export\HasExporterInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

/**
 * Provide capabilities to setup and dispatch a monitored job for report parts bulk, also provide the runner
 * to handle the export of the pdf file.
 *
 * This is to decouple service code from the job.
 */
class BulkReportJobService extends AbstractMonitoredJobService implements BulkReportJobServiceInterface, HasExporterInterface
{
    /**
     * @var BulkReportRepositoryInterface
     */
    private $bulkRepository;

    /**
     * @var BinRepositoryInterface
     */
    private $binRepository;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(
        BulkReportRepositoryInterface $bulkRepository,
        BinRepositoryInterface $binRepository,
        LoggerServiceInterface $logger,
        MonitoredJobRepositoryInterface $monitoredJobsRepository
    )
    {
        parent::__construct($monitoredJobsRepository);

        $this->bulkRepository = $bulkRepository;
        $this->binRepository = $binRepository;
        $this->logger = $logger;
    }

    /**
     * @param int $dealerId
     * @param array|BulkReportPayload $payload
     * @param string|null $token
     * @return BulkReport
     */
    public function setup(int $dealerId, $payload, ?string $token = null): BulkReport
    {
        return $this->bulkRepository->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => is_array($payload) ? $payload : $payload->asArray(),
            'queue' => BulkReport::QUEUE_NAME,
            'concurrency_level' => BulkReport::LEVEL_DEFAULT,
            'name' => BulkReport::QUEUE_JOB_NAME
        ]);
    }

    /**
     * Run the service
     *
     * @param BulkReport $job
     * @return mixed|void
     * @throws Throwable
     */
    public function run($job)
    {
        try {
            $this->logger->info(sprintf("[%s:] starting to export the pdf file for the monitored job '%s'", __CLASS__, $job->token));

            $this->bulkRepository->updateProgress($job->token, 0);

            $data = $this->getData($job);

            $this->bulkRepository->updateProgress($job->token, 10);

            // do the export
            $this->getExporter($job)
                ->withView($this->resolveView($job))
                ->withData($data)
                ->export();

            $this->bulkRepository->setCompleted($job->token);

            $this->logger->info(sprintf("[%s:] process to export the pdf file for the monitored job '%s' was completed", __CLASS__, $job->token));
        } catch (Throwable $exception) {
            $this->bulkRepository->setFailed($job->token, ['message' => "Got exception: {$exception->getMessage()}"]);
            $this->logger->error(sprintf('[%s:] got exception: %s', __CLASS__, $exception->getMessage()));

            throw $exception;
        }
    }

    /**
     * @param BulkReport $job
     * @return View
     * @throws UndefinedReportTypeException
     */
    protected function resolveView(BulkReport $job): View
    {
        // When there are more report types, they must be resolve in the following switch
        switch ($job->payload->type) {
            case BulkReport::TYPE_FINANCIALS:
                return view('prints.pdf.parts.financials-reports');
            // more types here
        }

        throw new UndefinedReportTypeException("There is not a '{$job->payload->type}' report type defined");
    }

    /**
     * @param BulkReport $job
     * @return array
     * @throws UndefinedReportTypeException
     */
    protected function getData(BulkReport $job): array
    {
        // When there are more report types, they must be resolve in the following switch
        switch ($job->payload->type) {
            case BulkReport::TYPE_FINANCIALS:
                return $this->binRepository->financialReportByDealer($job->dealer_id);
            // more types here
        }

        throw new UndefinedReportTypeException("There is not a '{$job->payload->type}' report type defined");
    }

    /**
     * @param BulkReport $job
     * @return FilesystemPdfExporter
     * @throws InvalidArgumentException when the job has a payload without a filename
     */
    public function getExporter($job): FilesystemPdfExporter
    {
        if ($job->payload->filename === '' || $job->payload->filename === null) {
            throw new InvalidArgumentException('This job has a payload without a filename');
        }

        return new FilesystemPdfExporter(Storage::disk('tmp'), $job->payload->filename);
    }
}
