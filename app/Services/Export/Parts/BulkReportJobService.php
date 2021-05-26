<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\Common\UndefinedReportTypeException;
use App\Models\Bulk\Parts\BulkReport;
use App\Models\Bulk\Parts\BulkReportPayload;
use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Repositories\Dms\StockRepositoryInterface;
use App\Repositories\Dms\ServiceOrder\ServiceItemTechnicianRepositoryInterface;
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
     * @var StockRepositoryInterface
     */
    private $stockRepository;
    
    /**
     * @var ServiceItemTechnicianRepositoryInterface
     */
    private $serviceTechnicianRepository;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(
        BulkReportRepositoryInterface $bulkRepository,
        StockRepositoryInterface $stockRepository,
        ServiceItemTechnicianRepositoryInterface $serviceTechnicianRepository,
        LoggerServiceInterface $logger,
        MonitoredJobRepositoryInterface $monitoredJobsRepository
    )
    {
        parent::__construct($monitoredJobsRepository);

        $this->bulkRepository = $bulkRepository;
        $this->stockRepository = $stockRepository;
        $this->serviceTechnicianRepository = $serviceTechnicianRepository;
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
            switch ($job->payload->type) {
                case BulkReport::TYPE_FINANCIALS:
                    // @todo: the progress calculation should be accurate using a better way
                    $this->logger->info(sprintf("[%s:] starting to export the pdf file for the monitored job '%s'", __CLASS__, $job->token));

                    $this->bulkRepository->updateProgress($job->token, 0);

                    $data = $this->getData($job);

                    $this->bulkRepository->updateProgress($job->token, 10);

                    // do the export
                    $this->getExporter($job)
                        ->withView($this->resolveView($job))
                        ->withData($data)
                        ->afterRender(function () use ($job) {
                            $this->bulkRepository->updateProgress($job->token, 15);
                        })
                        ->afterLoadHtml(function () use ($job) {
                            $this->bulkRepository->updateProgress($job->token, 95);
                        })
                        ->export();

                    $this->bulkRepository->setCompleted($job->token);

                    $this->logger->info(sprintf("[%s:] process to export the pdf file for the monitored job '%s' was completed", __CLASS__, $job->token));
                break;
                case BulkReport::TYPE_SERVICES:
                    $this->logger->info(sprintf("[%s:] starting to export the csv file for the monitored job '%s'", __CLASS__, $job->token));
                    
                    $this->bulkRepository->updateProgress($job->token, 0);

                    $data = $this->getData($job);

                    $step = round(count($data) / 80);
                    $progress = 10;
                    $csv_data = 'Technician,Ro Completed Date,RO Name,Sale Date,Paid Retail,Type,Invoice/Sale#,Customer,Act Hrs,Paid Hrs,Billed Hrs,Parts,Labor,Total (parts/labor),Cost,Profit,Margin' . PHP_EOL;

                    $this->bulkRepository->updateProgress($job->token, $progress);
                    foreach($data as $key => $row) {
                        $progress += $step;
                        foreach($row as $value) {
                            $this->bulkRepository->updateProgress($job->token, $progress);
                            
                            $current_cost = (float)$value['part_cost_amount'] + (float)$value['labor_cost_amount'];
                            $current_sale = (float)$value['part_sale_amount'] + (float)$value['labor_sale_amount'];
                            $profit = $current_sale - $current_cost;
                            $margin = ($current_cost != 0 && $current_sale != 0) ? number_format(($profit / $current_sale * 100), 2) . '%' : '';
    
                            $csv_data .= $value['first_name'] . ' ' . $value['last_name'] . ',' . $value['ro_completed_date'] . ',' . $value['ro_name'] . ',' . $value['sale_date'] . ',' . $value['paid_retail'] . ',' . $value['repair_order_type'] . ',' . $value['doc_num'] . ',' . $value['customer_name'] . ',' . $value['act_hrs'] . ',' . $value['paid_hrs'] . ',' . $value['billed_hrs'] . ',' . $value['part_sale_amount'] . ',' . $value['labor_sale_amount'] . ',' . ($value['part_sale_amount'] + $value['labor_sale_amount']) . ',' . $current_cost . ',' . $profit . ',' . $margin . PHP_EOL;
                        }
                    }
                    $this->bulkRepository->updateProgress($job->token, 95);

                    Storage::disk('tmp')->put($job->payload->filename, $csv_data);

                    $this->bulkRepository->setCompleted($job->token);

                    $this->logger->info(sprintf("[%s:] process to export the csv file for the monitored job '%s' was completed", __CLASS__, $job->token));
                break;
            }
        } catch (Throwable $exception) {
            $this->bulkRepository->setFailed($job->token, ['message' => "Got exception: {$exception->getMessage()} (line " . $exception->getLine() . ")"]);
            $this->logger->error(sprintf('[%s:] got exception: %s', __CLASS__, $exception->getMessage()), $exception->getTrace());

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
                $filters = ['dealer_id' => $job->dealer_id] + array_filter($job->payload->filters);

                return  $this->stockRepository->financialReport($filters);
            break;
            case BulkReport::TYPE_SERVICES:
                $filters = ['dealer_id' => $job->dealer_id] + array_filter($job->payload->filters);

                return $this->serviceTechnicianRepository->serviceReport($filters);
            break;
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
