<?php

namespace App\Console\Commands\Report;

use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use App\Domains\UserTracking\Types\UserTrackingEvent;
use App\Models\MonthlyImpressionReport;
use App\Models\UserTracking;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

class ReportMonthlyInventoryTrackingDataCommand extends Command
{
    public const CHUNK_SIZE = 10000;

    protected $signature = 'report:inventory:monthly-tracking-data';

    protected $description = 'Report the last month inventory data.';

    private Carbon $from;

    private Carbon $to;

    private int $year;

    private int $month;

    private array $inventories = [];

    private int $processedTotal = 0;

    public function __construct()
    {
        $this->from = now()->subMonth()->startOfMonth();
        $this->to = $this->from->clone()->endOfMonth();

        $this->year = $this->from->year;
        $this->month = $this->from->month;

        parent::__construct();
    }

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        MonthlyImpressionReport::query()
            ->where('year', $this->from->year)
            ->where('month', $this->from->month)
            ->delete();

        DB::table((new UserTracking())->getTable())
            ->whereBetween('created_at', [$this->from, $this->to])
            ->where('event', UserTrackingEvent::IMPRESSION)
            ->whereIn('page_name', GetPageNameFromUrlAction::PAGE_NAMES)
            ->chunkById(self::CHUNK_SIZE, function (Collection $userTrackings) {
                foreach ($userTrackings as $userTracking) {
                    $metaJson = json_decode($userTracking->meta);
                    foreach ($metaJson as $meta) {
                        $inventoryId = data_get($meta, 'inventory_id');
                        $dealerId = data_get($meta, 'dealer_id');
                        $totalColumn = match ($userTracking->page_name) {
                            GetPageNameFromUrlAction::PAGE_NAMES['TT_PLP'] => 'plp_total_count',
                            GetPageNameFromUrlAction::PAGE_NAMES['TT_PDP'] => 'pdp_total_count',
                            GetPageNameFromUrlAction::PAGE_NAMES['TT_DEALER'] => 'tt_dealer_page_total_count',
                            default => null,
                        };

                        if ($inventoryId === null || $dealerId === null || $totalColumn === null) {
                            continue;
                        }

                        // If this is a new record, we create it first
                        if (!array_key_exists($inventoryId, $this->inventories)) {
                            MonthlyImpressionReport::create([
                                'year' => $this->from->year,
                                'month' => $this->from->month,
                                'dealer_id' => $dealerId,
                                'inventory_id' => $inventoryId,
                                'inventory_title' => data_get($meta, 'title'),
                                'inventory_type' => data_get($meta, 'type_label'),
                                'inventory_category' => data_get($meta, 'category_label'),
                            ]);
                        }

                        // Then, we increment the total column count by 1
                        MonthlyImpressionReport::query()
                            ->where('year', $this->year)
                            ->where('month', $this->month)
                            ->where('inventory_id', $inventoryId)
                            ->increment($totalColumn);

                        // Save that we have stored this inventory
                        $this->inventories[$inventoryId] = true;
                    }

                    $this->processedTotal++;

                    $this->info("Finished processing one set of user tracking data: $this->processedTotal.");
                }

                $memoryUsage = memory_get_usage(true) / 1024 / 1024;

                $this->info('Finished ' . self::CHUNK_SIZE . " records, total: $this->processedTotal, memory usage: $memoryUsage MB.");
            });

        return 0;
    }
}
