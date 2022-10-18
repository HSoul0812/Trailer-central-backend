<?php

declare(strict_types=1);

namespace App\Services\Dms\ServiceOrder;

use App\Contracts\LoggerServiceInterface;
use App\Models\Bulk\Parts\BulkReport;
use App\Models\Common\MonitoredJob;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Repositories\Dms\ServiceOrder\ServiceItemTechnicianRepositoryInterface;
use App\Services\Export\FilesystemPdfExporter;
use App\Services\Common\AbstractMonitoredJobService;
use Exception;
use Illuminate\Support\Facades\Storage;

/**
 * Provide capabilities to setup and dispatch a monitored job for technician service order's cvs download.
 * Also provide the runner to handle the generation, write and export of the csv file.
 *
 * This is to decouple service code from the job.
 */

class BulkCsvTechnicianReportService extends AbstractMonitoredJobService implements BulkCsvTechnicianReportServiceInterface
{
    const QUEUE_NAME = 'reports';
    const QUEUE_JOB_NAME = 'technician-order-report';
    const TOTAL_PROGRESS_STEPS = 80; // must be equal or less than 100. It equals the segment of the 100% export process that is related to each row of the CSV
    const DEFAULT_PROGRESS_VALUE = 10;
    const COLUMNS_TITLES = 'Technician,Ro Completed Date,RO Name,Sale Date,Paid Retail,Type,Invoice/Sale#,Customer,Act Hrs,Paid Hrs,Billed Hrs,Parts,Labor,Total (parts/labor),Cost,Profit,Margin';

    /**
     * @var ServiceItemTechnicianRepositoryInterface
     */
    private $serviceTechnicianRepository;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(
        ServiceItemTechnicianRepositoryInterface $serviceTechnicianRepository,
        LoggerServiceInterface $logger,
        MonitoredJobRepositoryInterface $monitoredJobsRepository
    )
    {
        parent::__construct($monitoredJobsRepository);

        $this->serviceTechnicianRepository = $serviceTechnicianRepository;
        $this->logger = $logger;
    }

    /**
     * @param int $dealerId
     * @param array|BulkReportPayload $payload
     * @param string|null $token
     * @return MonitoredJob
     */
    public function setup(int $dealerId, $payload, ?string $token = null): MonitoredJob
    {
        return $this->repository->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => is_array($payload) ? $payload : $payload->asArray(),
            'queue' => self::QUEUE_NAME,
            'concurrency_level' => MonitoredJob::LEVEL_DEFAULT,
            'name' => self::QUEUE_JOB_NAME
        ]);
    }

    /**
     * Run the service
     *
     * @param BulkReport $job
     * @return mixed|void
     * @throws Exception
     */
    public function run($job)
    {
        $filters = ['dealer_id' => $job->dealer_id] + array_filter($job->payload->filters);
        $data = $this->serviceTechnicianRepository->serviceReport($filters);

        $step = round(count($data) / self::TOTAL_PROGRESS_STEPS);
        $progress = self::DEFAULT_PROGRESS_VALUE;
        $csv_data = self::COLUMNS_TITLES . PHP_EOL;

        $this->repository->updateProgress($job->token, $progress);
        foreach($data as $key => $row) {
            $progress += $step;
            foreach($row as $value) {
                $this->repository->updateProgress($job->token, $progress);

                $current_cost = (float)$value['part_cost_amount'] + (float)$value['labor_cost_amount'];
                $current_sale = (float)$value['part_sale_amount'] + (float)$value['labor_sale_amount'];
                $profit = $current_sale - $current_cost;
                $margin = ($current_cost != 0 && $current_sale != 0) ? number_format(($profit / $current_sale * 100), 2) . '%' : '';

                $csv_data .= $value['first_name'] . ' ' . $value['last_name'] . ',' . $value['ro_completed_date'] . ',' . $value['ro_name'] . ',' . $value['sale_date'] . ',' . $value['paid_retail'] . ',' . $value['repair_order_type'] . ',' . $value['doc_num'] . ',' . $value['customer_name'] . ',' . $value['act_hrs'] . ',' . $value['paid_hrs'] . ',' . $value['billed_hrs'] . ',' . $value['part_sale_amount'] . ',' . $value['labor_sale_amount'] . ',' . ($value['part_sale_amount'] + $value['labor_sale_amount']) . ',' . $current_cost . ',' . $profit . ',' . $margin . PHP_EOL;
            }
        }
        $this->repository->updateProgress($job->token, 95);

        Storage::disk('s3')->put(FilesystemPdfExporter::RUNTIME_PREFIX . $job->payload->filename, $csv_data);

        $this->repository->setCompleted($job->token);
    }
}
