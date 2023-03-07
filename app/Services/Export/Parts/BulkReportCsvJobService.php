<?php

namespace App\Services\Export\Parts;

use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Bulk\Parts\BulkDownloadPayload;
use App\Repositories\Bulk\Parts\BulkDownloadRepositoryInterface;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Repositories\Dms\StockRepositoryInterface;
use App\Services\Common\AbstractMonitoredJobService;
use App\Services\Export\FilesystemPdfExporter;
use App\Services\Export\HasExporterInterface;
use App\Services\Export\ManualFilesystemCsvExporter;
use Exception;
use Illuminate\Support\Facades\Storage;

class BulkReportCsvJobService extends AbstractMonitoredJobService implements BulkReportCsvJobServiceInterface, HasExporterInterface
{
    /** @var string */
    const TYPE_INVENTORIES = 'inventories';

    /** @var string */
    const TYPE_PARTS = 'parts';

    /** @var string */
    const TYPE_MIXED = 'mixed';

    /** @var int[] */
    const UPDATE_PROGRESS_STEPS = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100];

    /**
     * @var BulkDownloadRepositoryInterface
     */
    private $bulkRepository;

    /**
     * @var StockRepositoryInterface
     */
    protected $stockRepository;

    public function __construct(
        BulkDownloadRepositoryInterface $bulkRepository,
        MonitoredJobRepositoryInterface $monitoredJobsRepository,
        StockRepositoryInterface $stockRepository
    )
    {
        parent::__construct($monitoredJobsRepository);

        $this->bulkRepository = $bulkRepository;
        $this->stockRepository = $stockRepository;
    }

    /**
     * @param int $dealerId
     * @param array|BulkDownloadPayload $payload
     * @param string|null $token
     * @return BulkDownload
     */
    public function setup(int $dealerId, $payload, ?string $token = null): BulkDownload
    {
        return $this->bulkRepository->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => is_array($payload) ? $payload : $payload->asArray(),
            'queue' => BulkDownload::QUEUE_NAME,
            'concurrency_level' => BulkDownload::LEVEL_DEFAULT,
            'name' => BulkDownload::QUEUE_JOB_NAME
        ]);
    }

    /**
     * Run the service
     *
     * @param BulkDownload $job
     * @return void
     * @throws Exception
     */
    public function run($job): void
    {
        $exporter = $this->getExporter($job);

        $reports = $this->getReports($job);
        $totalReport = count($reports);
        $currentProcessed = 0;
        $currentUpdateProgressStepIndex = 0;
        $typeOfStock = $job->payload->filters['type_of_stock'];

        // Write the header line
        $headerLine = $this->headerLine();
        $exporter->writeLine($headerLine);

        // Start looping through each item in the report
        // we deal with parts and inventories item differently
        foreach ($reports as $idAndType => $items) {
            // The $idAndType looks like this '303-parts'
            // or '192-inventories' if it's a inventory
            list($id, $type) = explode('-', $idAndType);

            $countBins = 0;

            foreach ($items as $bins) {
                foreach ($bins as $bin) {
                    // Inventory doesn't have bin,we can write it to the file
                    // without additional logic
                    $line = [];

                    if ($type === self::TYPE_INVENTORIES) {
                        // For inventory line, we don't want to print the bin name if we
                        // don't need to (user only selects to print the inventory for example)
                        $line = $this->inventoryLine($bin);
                    }

                    if ($type === self::TYPE_PARTS) {
                        // We only write full line as the first line of a part
                        $line = $this->partLine($bin, $countBins === 0);
                    }

                    if (!empty($line)) {
                        $exporter->writeLine($line);
                    }
                }

                $countBins++;
                $currentProcessed++;

                // The logic to update the progress, we'll keep the progress updating
                // whenever the processed percentage hits the next index in the
                // self::UPDATE_PROGRESS_STEPS array
                $percentage = $totalReport / $currentProcessed;
                $currentRequiredPercentageToUpdateProgress = self::UPDATE_PROGRESS_STEPS[$currentUpdateProgressStepIndex];
                if ($percentage < $currentRequiredPercentageToUpdateProgress) {
                    continue;
                }

                $this->bulkRepository->updateProgress($job->token, $currentRequiredPercentageToUpdateProgress);
                $currentUpdateProgressStepIndex++;
            }

            $exporter->export($this->filePath($job));
        }

        $this->bulkRepository->setCompleted($job->token);
    }

    /**
     * @param object $job
     * @return array
     */
    private function getReports($job): array
    {
        $filters = $job->payload->filters;

        return $this->stockRepository->financialReport([
            'type_of_stock' => $filters['type_of_stock'],
            'dealer_id' => $job->dealer_id,
            'to_date' => $filters['date'],
            'search_term' => $filters['search_term'] ?? null,
        ]);
    }

    /**
     * @param object $job
     * @return string
     */
    private function filePath(object $job): string
    {
        return FilesystemPdfExporter::RUNTIME_PREFIX . $job->payload->filename;
    }

    /**
     * Get the line array for the header
     *
     * @return array
     */
    private function headerLine(): array
    {
        return ['Sku/Stock', 'Title', 'Cost', 'Price', 'Bin Name', 'Qty', 'Total Cost', 'Total Price'];
    }

    /**
     * Get the exporter to use for this service
     *
     * @param object $job
     * @return ManualFilesystemCsvExporter
     */
    public function getExporter($job): ManualFilesystemCsvExporter
    {
        $disk = Storage::disk('s3');

        return new ManualFilesystemCsvExporter($disk);
    }

    /**
     * Get the line array for the inventory row
     *
     * @param object $bin
     * @return array
     */
    private function inventoryLine(object $bin): array
    {
        return [
            $bin->reference,
            $bin->title,
            $bin->dealer_cost,
            $bin->price,
            '',
            $bin->qty,
            $bin->dealer_cost * $bin->qty,
            $bin->price * $bin->qty,
        ];
    }

    /**
     * Get the line array for part
     * @param $bin
     * @param bool $fullLine
     * @return array
     */
    private function partLine($bin, bool $fullLine): array
    {
        if (!$fullLine) {
            $line = array_fill(0, 4, '');
        } else {
            $line = [
                $bin->reference,
                $bin->title,
                $bin->dealer_cost,
                $bin->price,
            ];
        }

        return array_merge($line, [
            $bin->bin_name,
            $bin->qty,
            $bin->dealer_cost * $bin->qty,
            $bin->price * $bin->qty,
        ]);
    }

    /**
     * Determine if we should output the bin name to the CSV file or not
     *
     * @param string $typeOfStock
     * @return bool
     */
    private function shouldOutputBinName(string $typeOfStock): bool
    {
        return in_array($typeOfStock, [self::TYPE_PARTS, self::TYPE_MIXED]);
    }
}
