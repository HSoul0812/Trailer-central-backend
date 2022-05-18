<?php

namespace App\Jobs\Export;

use App\Mail\Export\FavoritesExportMail;
use App\Models\Export\WebsiteFavoritesExport;
use App\Models\Website\Config\WebsiteConfig;
use App\Repositories\Export\FavoritesRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Services\Export\Favorites\CustomerCsvExporterInterface;
use App\Services\Export\Favorites\InventoryCsvExporterInterface;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ExportFavoritesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const EXPORT_DAILY = 0;
    const EXPORT_WEEKLY = 1;
    const EXPORT_BI_WEEKLY = 2;
    const EXPORT_MONTHLY = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @param WebsiteConfig $config
     * @param WebsiteFavoritesExport $history
     * @return bool
     */
    private function shouldExport(WebsiteConfig $config, WebsiteFavoritesExport $history): bool
    {
        switch ((int)$config->value) {
            case self::EXPORT_DAILY:
                return Carbon::parse($history->last_ran)->isYesterday();
            case self::EXPORT_WEEKLY:
                return now()->diffInDays(Carbon::parse($history->last_ran)) == 7;
            case self::EXPORT_BI_WEEKLY:
                return now()->diffInDays(Carbon::parse($history->last_ran)) == 14;
            case self::EXPORT_MONTHLY:
                return now()->diffInMonths(Carbon::parse($history->last_ran)) == 1;
            default:
                return false;
        }
    }

    /**
     * Sanitize emails
     * @param string $config
     * @return array
     */
    private function sanitizeEmails(string $config): array
    {
        $emails = json_decode($config)->value;
        return array_map('trim', explode(';', $emails));
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function handle(WebsiteConfigRepositoryInterface $websiteConfigRepository, FavoritesRepositoryInterface $favoritesRepository, CustomerCsvExporterInterface $customerExporter, InventoryCsvExporterInterface $inventoryExporter)
    {
        $websites = $websiteConfigRepository->getAll([
            'key' => 'general/favorites_export_schedule'
        ]);

        $websiteEmails = $websiteConfigRepository->getAll([
            'key' => 'general/favorites_export_emails'
        ]);

        $history = WebsiteFavoritesExport::all();

        $websites->each(function ($websiteConfig) use (
            $history,
            $websiteEmails,
            $customerExporter,
            $inventoryExporter,
            $favoritesRepository
        ) {
            $shouldExportNow = false;
            $websiteHistory = $history->firstWhere('website_id', $websiteConfig->website_id);
            if ($websiteHistory) {
                $shouldExportNow = $this->shouldExport($websiteConfig, $websiteHistory);
            }
            if (!$websiteHistory || $shouldExportNow) {
                $emails = $websiteEmails->firstWhere('website_id', $websiteConfig->website_id);
                if ($emails) {
                    $data = $favoritesRepository->get(['website_id' => $websiteConfig->website_id]);

                    $inventoryData = $data->map(function ($user) {
                        return $user->favoriteInventories->map(function ($favorite) {
                            return $favorite->inventory;
                        });
                    })->flatten();

                    $customerCsv = $customerExporter->export($data);
                    $inventoryCsv = $inventoryExporter->export($inventoryData);

                    Mail::send(new FavoritesExportMail($this->sanitizeEmails($emails), $customerCsv, $inventoryCsv));

                    WebsiteFavoritesExport::logRun($websiteConfig->website_id);
                }
            }
        });
    }
}
