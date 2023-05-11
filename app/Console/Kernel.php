<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\Crawlers\CacheCrawlerIpAddressesCommand;
use App\Console\Commands\UserTracking\ProcessUserTrackingsCommand;
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

        $schedule->command('sync:leads')
            ->daily()
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('db:refresh-views')
            ->daily()
            ->withoutOverlapping()
            ->runInBackground();

        $schedule
            ->command(CacheCrawlerIpAddressesCommand::class)
            ->daily()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/commands/cache-crawler-ip-addresses.log'));

        $schedule
            ->command(ProcessUserTrackingsCommand::class)
            ->daily()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();
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
