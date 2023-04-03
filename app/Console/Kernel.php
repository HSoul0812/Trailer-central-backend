<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\Crawlers\CacheCrawlerIpAddressesCommand;
use App\Console\Commands\Images\DeleteOldLocalImagesCommand;
use App\Console\Commands\Report\ReportInventoryViewAndImpressionCommand;
use App\Console\Commands\UserTracking\PopulateMissingWebsiteUserIdCommand;
use App\Console\Commands\UserTracking\PopulateUserLocationCommand;
use Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sync:inventory')
            ->daily()
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('inventory:hide-expired')
            ->daily()
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('sync:leads')
            ->daily()
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('db:refresh-views')
            ->daily()
            ->withoutOverlapping()
            ->runInBackground();

        $schedule
            ->command(PopulateUserLocationCommand::class)
            ->daily()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground()
            ->before(function() {
                // TODO: Set the logger here
            })
            ->after(function() {
                Artisan::call(PopulateMissingWebsiteUserIdCommand::class, [
                    'date' => now()->subDay()->format(PopulateMissingWebsiteUserIdCommand::DATE_FORMAT),
                ]);
                Artisan::call(ReportInventoryViewAndImpressionCommand::class, [
                    'date' => now()->subMinutes(10)->format(ReportInventoryViewAndImpressionCommand::DATE_FORMAT),
                ]);
            });

        $schedule
            ->command(DeleteOldLocalImagesCommand::class)
            ->daily()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/commands/delete-old-local-images.log'));

        $schedule
            ->command(CacheCrawlerIpAddressesCommand::class)
            ->daily()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/commands/cache-crawler-ip-addresses.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
