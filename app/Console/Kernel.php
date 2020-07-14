<?php

namespace App\Console;

use App\Console\Commands\Website\AddSitemaps;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SyncPartsCommand;
use App\Console\Commands\RunBulkUploadCommand;
use App\Console\Commands\ReplaceYoutubeEmbeds;
use App\Console\Commands\AdjustFeetAndInches;
use App\Console\Commands\User\CreateAccessToken;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ReplaceYoutubeEmbeds::class,
        SyncPartsCommand::class,
        RunBulkUploadCommand::class,
        AddSitemaps::class,
        AdjustFeetAndInches::class,
        CreateAccessToken::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('run:bulk')->withoutOverlapping();
        $schedule->command('add:sitemaps')->daily();
        $schedule->command('user:create-access-token')->daily();
        //$schedule->command('leads:assign:auto')->withoutOverlapping();
        //$schedule->command('leads:assign:hotpotato')->withoutOverlapping();
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
