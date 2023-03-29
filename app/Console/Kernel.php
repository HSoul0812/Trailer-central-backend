<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\Crawlers\CacheCrawlerIpAddressesCommand;
use App\Console\Commands\Images\DeleteOldLocalImagesCommand;
use App\Console\Commands\Report\ReportInventoryViewAndImpressionCommand;
use App\Console\Commands\UserTracking\PopulateMissingWebsiteUserIdCommand;
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
            ->command(PopulateMissingWebsiteUserIdCommand::class, [
                // Send the yesterday time to the command
                'date' => now()->subMinutes(10)->format(PopulateMissingWebsiteUserIdCommand::DATE_FORMAT),
            ])
            ->daily()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        $schedule
            ->command(ReportInventoryViewAndImpressionCommand::class, [
                // Send the yesterday time to the command
                'date' => now()->subMinutes(10)->format(ReportInventoryViewAndImpressionCommand::DATE_FORMAT),
            ])
            ->daily()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

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
