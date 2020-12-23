<?php

namespace App\Console;

use App\Console\Commands\Website\AddSitemaps;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SyncPartsCommand;
use App\Console\Commands\Parts\Import\RunBulkUploadCommand;
use App\Console\Commands\ReplaceYoutubeEmbeds;
use App\Console\Commands\Inventory\AdjustFeetAndInches;
use App\Console\Commands\User\CreateAccessToken;
use App\Console\Commands\Parts\Import\StocksExistsCommand;
use App\Console\Commands\CRM\Leads\AutoAssign; 
use App\Console\Commands\Parts\IncreaseDealerCostCommand;

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
        CreateAccessToken::class,
        StocksExistsCommand::class,
        AutoAssign::class,
        IncreaseDealerCostCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('run:bulk')
                ->withoutOverlapping()
                ->runInBackground(); 
        
        $schedule->command('add:sitemaps')
                ->daily()
                ->runInBackground();
        
        $schedule->command('user:create-access-token')
                ->daily()
                ->runInBackground();
        
        $schedule->command('crm:dms:update-po-num-ref')
                ->daily()
                ->runInBackground();
        
        $schedule->command('leads:assign:auto 0 2999')
                ->withoutOverlapping()
                ->runInBackground();
        
        $schedule->command('leads:assign:auto 3000 5999')
                ->withoutOverlapping()
                ->runInBackground();
        
        $schedule->command('leads:assign:auto 6000 8999')
                ->withoutOverlapping()
                ->runInBackground();
        
        $schedule->command('leads:assign:auto 8999')
                ->withoutOverlapping()
                ->runInBackground();
        
        $schedule->command('leads:assign:auto 0 0 8770')
                ->withoutOverlapping()
                ->runInBackground();
        
        //$schedule->command('leads:assign:hotpotato')->withoutOverlapping();
        
        $schedule->command('text:process-campaign')
                ->withoutOverlapping()
                ->runInBackground();
        
        $schedule->command('text:deliver-blast')
                ->withoutOverlapping()
                ->runInBackground();


        /**
         * Scrape Replies
         */
        // 0 - 1098
        $schedule->command('email:scrape-replies 0 1098')
                ->withoutOverlapping()
                ->runInBackground();

        // 1099 (super long, so run it separately)
        $schedule->command('email:scrape-replies 0 0 1099')
                ->withoutOverlapping()
                ->runInBackground();

        // 1100 - 2999
        $schedule->command('email:scrape-replies 1100 2999')
                ->withoutOverlapping()
                ->runInBackground();

        // 3000 - 5999
        $schedule->command('email:scrape-replies 3000 5999')
                ->withoutOverlapping()
                ->runInBackground();

        // 6000 - 8999
        $schedule->command('email:scrape-replies 6000 8999')
                ->withoutOverlapping()
                ->runInBackground();

        // 9000+
        $schedule->command('email:scrape-replies 9000')
                ->withoutOverlapping()
                ->runInBackground();

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
