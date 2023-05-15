<?php

namespace App\Console\Commands\Report;

use App\Domains\Commands\Traits\PrependsOutput;
use App\Domains\Commands\Traits\PrependsTimestamp;
use App\Domains\Compression\Actions\CompressFileWithGzipAction;
use App\Domains\Compression\Exceptions\GzipFailedException;
use App\Models\MonthlyImpressionReport;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Storage;

class GenerateMonthlyInventoryTrackingDataReportCommand extends Command
{
    use PrependsTimestamp;
    use PrependsOutput;

    public const DEALER_CHUNK = 1000;

    protected $signature = '
        report:inventory:monthly-tracking-data
        {year? : The year to run this report.}
        {month? : The month to run this report.}
    ';

    protected $description = 'Report the last month inventory data.';

    private Carbon $date;

    private FilesystemAdapter $storage;

    public function __construct(private CompressFileWithGzipAction $compressFileWithGzipAction)
    {
        $this->storage = Storage::disk('monthly-inventory-impression-reports');

        parent::__construct();
    }

    /**
     * @throws GzipFailedException
     */
    public function handle(): int
    {
        try {
            $this->validate();
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $this->info("Command $this->name is running.");

        $this->info("Input: Year {$this->date->year}, Month {$this->date->month}.");

        $this->deleteExistingData();

        $this->info('Existing data removed.');

        $this->exportData();

        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;

        $this->info("Command $this->name is finished. Memory usage: $memoryUsage MB.");

        return 0;
    }

    /**
     * @throws Exception
     */
    private function validate(): void
    {
        $now = now()->startOfMonth();
        $yearInput = $this->argument('year');
        $monthInput = $this->argument('month');

        if ($yearInput !== null || $monthInput !== null) {
            if (filter_var($yearInput, FILTER_VALIDATE_INT) === false || filter_var($monthInput, FILTER_VALIDATE_INT) === false) {
                throw new Exception('Year and month must be an integer.');
            }
        }

        $year = intval($this->argument('year') ?? $now->year);
        $month = intval($this->argument('month') ?? ($now->month - 1));

        $this->date = Carbon::createFromDate($year, $month)->startOfMonth();

        if ($this->date->gte($now)) {
            throw new Exception('You can only generate the report up to last month.');
        }
    }

    private function deleteExistingData(): void
    {
        // Delete directory from the storage
        $directory = sprintf('%d/%02d', $this->date->year, $this->date->month);

        $this->storage->deleteDirectory($directory);
    }

    /**
     * @throws GzipFailedException
     */
    private function exportData(): void
    {
        MonthlyImpressionReport::query()
            ->year($this->date->year)
            ->month($this->date->month)
            ->distinct()
            ->get(['dealer_id'])
            ->pluck('dealer_id')
            ->each(fn (int $dealerId) => $this->exportCsvForDealerId($dealerId));
    }

    /**
     * @throws GzipFailedException
     */
    private function exportCsvForDealerId(int $dealerId): void
    {
        $filePath = $this->filePath($dealerId);

        // This is just for making sure that we have the proper folder created
        $this->storage->put($filePath, '');

        $csvFilePath = $this->storage->path($filePath);

        $csvFile = fopen($csvFilePath, 'w');

        fputcsv($csvFile, $this->csvHeaderRow());

        MonthlyImpressionReport::query()
            ->yearMonthDealerId($this->date->year, $this->date->month, $dealerId)
            ->chunkById(self::DEALER_CHUNK, function (Collection $monthlyImpressionReports) use ($csvFile) {
                foreach ($monthlyImpressionReports as $monthlyImpressionReport) {
                    fputcsv(
                        stream: $csvFile,
                        fields: $this->monthlyImpressionReportToCsvRow($monthlyImpressionReport),
                    );
                }
            });

        fclose($csvFile);

        $zipFilePath = $this->compressFileWithGzipAction->execute($csvFilePath);

        $this->info("Zip file created for Dealer ID: $dealerId, zip location: $zipFilePath.");

        // Delete the csv file, so we have only the zip file left
        @unlink($csvFilePath);
    }

    private function filePath(int $dealerId): string
    {
        return sprintf('%d/%02d/dealer-id-%d.csv', $this->date->year, $this->date->month, $dealerId);
    }

    private function csvHeaderRow(): array
    {
        return [
            'Inventory ID',
            'Inventory Title',
            'Inventory Type',
            'Inventory Category',
            'PLP Total Count',
            'PDP Total Count',
            'Dealer Page Total Count',
        ];
    }

    private function monthlyImpressionReportToCsvRow(MonthlyImpressionReport $monthlyImpressionReport): array
    {
        return [
            $monthlyImpressionReport->inventory_id,
            $monthlyImpressionReport->inventory_title ?? 'N/A',
            $monthlyImpressionReport->inventory_type ?? 'N/A',
            $monthlyImpressionReport->inventory_category ?? 'N/A',
            $monthlyImpressionReport->plp_total_count,
            $monthlyImpressionReport->pdp_total_count,
            $monthlyImpressionReport->tt_dealer_page_total_count,
        ];
    }
}
