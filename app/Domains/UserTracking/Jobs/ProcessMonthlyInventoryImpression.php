<?php

namespace App\Domains\UserTracking\Jobs;

use App\Domains\Jobs\JobQueue;
use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use App\Domains\UserTracking\Types\UserTrackingEvent;
use App\Models\MonthlyImpressionReport;
use App\Models\UserTracking;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Str;

class ProcessMonthlyInventoryImpression implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected UserTracking $userTracking)
    {
        $this->onQueue(JobQueue::USER_TRACKINGS);
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        // Do not process if the page name is not one of the page that we want to process
        if (!in_array($this->userTracking->page_name, GetPageNameFromUrlAction::PAGE_NAMES)) {
            return;
        }

        // Do not process if the event type is not impression
        if ($this->userTracking->event !== UserTrackingEvent::IMPRESSION) {
            return;
        }

        // Also do not process if the meta is null
        if ($this->userTracking->meta === null) {
            return;
        }

        $pageName = $this->userTracking->page_name;

        $site = data_get(GetPageNameFromUrlAction::PAGE_NAME_TO_SITE, $pageName, GetPageNameFromUrlAction::SITE_TT_AF);

        $year = $this->userTracking->created_at->year;
        $month = $this->userTracking->created_at->month;

        foreach ($this->userTracking->meta as $meta) {
            $dealerId = data_get($meta, 'dealer_id');
            $inventoryId = data_get($meta, 'inventory_id');
            $inventoryTitle = data_get($meta, 'title');
            $inventoryType = data_get($meta, 'type_label');
            $inventoryCategory = data_get($meta, 'category_label');
            $totalCountColumn = data_get(GetPageNameFromUrlAction::PAGE_NAME_TO_TOTAL_COUNT_COLUMN, $this->userTracking->page_name);

            // List of fields that we don't want to process if any of them
            // is null
            $requiredFields = [
                $dealerId,
                $inventoryId,
                $totalCountColumn,
            ];

            // We don't want to process if any of the required fields is null
            if (in_array(null, $requiredFields)) {
                continue;
            }

            $site = data_get(GetPageNameFromUrlAction::PAGE_NAME_TO_SITE, $this->userTracking->page_name, GetPageNameFromUrlAction::SITE_TT_AF);

            $rowExists = MonthlyImpressionReport::query()
                ->site($site)
                ->year($year)
                ->month($month)
                ->where('inventory_id', $inventoryId)
                ->exists();

            // Create a record if it doesn't exist
            if (!$rowExists) {
                try {
                    MonthlyImpressionReport::create([
                        'year' => $year,
                        'month' => $month,
                        'dealer_id' => $dealerId,
                        'inventory_id' => $inventoryId,
                        'inventory_title' => $inventoryTitle,
                        'inventory_type' => $inventoryType,
                        'inventory_category' => $inventoryCategory,
                        'plp_total_count' => 0,
                        'pdp_total_count' => 0,
                        'tt_dealer_page_total_count' => 0,
                        'site' => $site,
                    ]);
                } catch (Exception $exception) {
                    // We throw exception only if it's not the duplicate error
                    if (!Str::of($exception->getMessage())->contains('monthly_impression_reports_site_year_month_inventory_id_unique')) {
                        // We sleep for 1 - 5 seconds (random) before throw out the exception and the queue
                        // worker will process this job again
                        sleep(random_int(1, 5));

                        throw $exception;
                    }
                }
            }

            MonthlyImpressionReport::query()
                ->site($site)
                ->year($year)
                ->month($month)
                ->where('inventory_id', $inventoryId)
                ->increment($totalCountColumn);
        }
    }
}
