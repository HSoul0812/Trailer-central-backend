<?php

namespace App\Console\Commands\Report;

use App\Domains\Commands\Traits\PrependsOutput;
use App\Domains\Commands\Traits\PrependsTimestamp;
use App\Domains\Compression\Actions\CompressFileWithGzipAction;
use App\Domains\Compression\Exceptions\GzipFailedException;
use App\Models\MonthlyImpressionCounting;
use App\Models\MonthlyImpressionReport;
use DB;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Storage;
use Str;

class GenerateMonthlyImpressionCountingsReportCommand extends Command
{
    use PrependsTimestamp;
    use PrependsOutput;

    public const DEALER_CHUNK = 1000;

    protected $signature = '
        report:inventory:monthly-impression-countings
        {year? : The year to run this report.}
        {month? : The month to run this report.}
    ';

    protected $description = 'Report the last month inventory impression counting data.';

    private Carbon $date;

    private FilesystemAdapter $storage;

    public function __construct(private CompressFileWithGzipAction $compressFileWithGzipAction)
    {
        $this->storage = Storage::disk('monthly-inventory-impression-countings-reports');

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

        if (!is_int($this->argument('year')) || !is_int($this->argument('month'))) {
            throw new Exception('Year and month must be integer.');
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
        // Clear the data from DB for the selected month
        MonthlyImpressionCounting::query()
            ->year($this->date->year)
            ->month($this->date->month)
            ->delete();

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
            ->each(fn (int $dealerId) => $this->processForDealerId($dealerId));
    }

    /**
     * @throws GzipFailedException
     */
    private function processForDealerId(int $dealerId): void
    {
        // 1. Export zip file and get the path, so we can store it in DB
        $zipFilePath = $this->exportInventoryImpressionSummaryToCsv($dealerId);

        // 2. Calculate and store data in the database
        $this->storeTotalCountings($dealerId, $zipFilePath);
    }

    /**
     * @throws GzipFailedException
     */
    private function exportInventoryImpressionSummaryToCsv(int $dealerId): string
    {
        $filePath = $this->filePath($dealerId);

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

        return $zipFilePath;
    }

    private function filePath(int $dealerId): string
    {
        return sprintf('%d/%02d/dealer-id-%d.csv', $this->date->year, $this->date->month, $dealerId);
    }

    private function storeTotalCountings(int $dealerId, string $zipFilePath): void
    {
        MonthlyImpressionCounting::create([
            'year' => $this->date->year,
            'month' => $this->date->month,
            'dealer_id' => $dealerId,
            'impressions_count' => $this->impressionsCount($dealerId),
            'views_count' => $this->viewsCount($dealerId),
            'zip_file_path' => $this->relativeZipFilePath($zipFilePath),
        ]);
    }

    private function csvHeaderRow(): array
    {
        return [
            'Inventory ID',
            'Impressions Total',
            'Views Total',
        ];
    }

    private function monthlyImpressionReportToCsvRow(MonthlyImpressionReport $monthlyImpressionReport): array
    {
        return [
            $monthlyImpressionReport->inventory_id,
            $monthlyImpressionReport->plp_total_count,

            // Per requirement from https://operatebeyond.atlassian.net/browse/TR-835
            // Views is the pdp + dealer page count
            $monthlyImpressionReport->pdp_total_count + $monthlyImpressionReport->tt_dealer_page_total_count,
        ];
    }

    private function impressionsCount(int $dealerId): int
    {
        return MonthlyImpressionReport::query()
            ->yearMonthDealerId($this->date->year, $this->date->month, $dealerId)
            ->groupBy('dealer_id')
            ->sum('plp_total_count');
    }

    private function viewsCount(int $dealerId): int
    {
        return MonthlyImpressionReport::query()
            ->yearMonthDealerId($this->date->year, $this->date->month, $dealerId)
            ->groupBy('dealer_id')
            ->select(DB::raw('(SUM(pdp_total_count) + SUM(tt_dealer_page_total_count)) as sum'))
            ->first()
            ->getAttribute('sum');
    }

    private function relativeZipFilePath(string $zipFilePath): Stringable
    {
        return Str::of($zipFilePath)->remove(
            search: $this->storage->path(''),
        );
    }
}
