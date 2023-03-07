<?php

namespace App\Console\Commands\Export;

use App\Mail\Export\FavoritesExportMail;
use App\Models\Export\WebsiteFavoritesExport;
use App\Models\Website\Config\WebsiteConfig;
use App\Repositories\Export\FavoritesRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Services\Export\Favorites\InventoryCsvExporterInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Mail;

class ExportFavoritesCommand extends Command
{
    const EXPORT_DAILY = 0;
    const EXPORT_WEEKLY = 1;
    const EXPORT_BI_WEEKLY = 2;
    const EXPORT_MONTHLY = 3;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:inventory-favorites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will export and send favorite inventory data to the setup emails';

    /**
     * @param WebsiteConfig $config
     * @param WebsiteFavoritesExport $history
     * @return bool
     */
    private function shouldExport(WebsiteConfig $config, WebsiteFavoritesExport $history): bool
    {
        switch ((int)$config->value) {
            case self::EXPORT_DAILY:
                return !Carbon::parse($history->last_ran)->isToday();
            case self::EXPORT_WEEKLY:
                return now()->diffInDays(Carbon::parse($history->last_ran)) >= 7;
            case self::EXPORT_BI_WEEKLY:
                return now()->diffInDays(Carbon::parse($history->last_ran)) >= 14;
            case self::EXPORT_MONTHLY:
                return now()->diffInMonths(Carbon::parse($history->last_ran)) >= 1;
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
    public function handle(WebsiteConfigRepositoryInterface $websiteConfigRepository, FavoritesRepositoryInterface $favoritesRepository)
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
            $favoritesRepository
        ) {
            $inventoryExporter = app()->make(InventoryCsvExporterInterface::class);

            $shouldExportNow = false;
            $websiteHistory = $history->firstWhere('website_id', $websiteConfig->website_id);
            if ($websiteHistory) {
                $shouldExportNow = $this->shouldExport($websiteConfig, $websiteHistory);
            }
            if (!$websiteHistory || $shouldExportNow) {
                $emails = $websiteEmails->firstWhere('website_id', $websiteConfig->website_id);
                if ($emails && $emails->value) {
                    $data = $favoritesRepository->get(['website_id' => $websiteConfig->website_id]);

                    $inventoryData = $data->map(function ($user) {
                        return $user->favoriteInventories->map(function ($favorite) use ($user) {
                            $inventory = $favorite->inventory;
                            $inventory->setAttribute('user', $user);
                            return $inventory;
                        });
                    })->flatten();

                    $inventoryCsv = $inventoryExporter->export($inventoryData);

                    Mail::send(new FavoritesExportMail($this->sanitizeEmails($emails), $inventoryCsv));

                    WebsiteFavoritesExport::logRun($websiteConfig->website_id);
                }
            }
        });
    }

}
