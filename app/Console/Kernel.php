<?php

namespace App\Console;

use App\Console\Commands\CRM\Interactions\ReimportInteractionMessages;
use App\Console\Commands\CRM\Interactions\ResetInteractionMessages;
use App\Console\Commands\CRM\Leads\RemoveBrokenCharacters;
use App\Console\Commands\Files\ClearLocalTmpFolder;
use App\Console\Commands\Inventory\AutoArchiveSoldItems;
use App\Console\Commands\MyScheduleWorkCommand;
use App\Console\Commands\Website\AddSitemaps;
use App\Console\Commands\Website\GenerateDealerSpecificSiteUrls;
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
use App\Console\Commands\Parts\FixPartVendor;
use App\Console\Commands\CRM\Dms\CVR\GenerateCVRDocumentCommand;
use App\Console\Commands\CRM\Dms\UnitSale\GetCompletedSaleWithNoFullInvoice;
use App\Console\Commands\CRM\Dms\UnitSale\FixEmptyManufacturerUnitSale;
use App\Console\Commands\Inventory\FixFloorplanBillStatus;
use App\Console\Commands\Parts\Import\GetTextrailParts;

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
        IncreaseDealerCostCommand::class,
        FixPartVendor::class,
        GenerateCVRDocumentCommand::class,
        GetCompletedSaleWithNoFullInvoice::class,
        ClearLocalTmpFolder::class,
        GenerateDealerSpecificSiteUrls::class,
        AutoArchiveSoldItems::class,
        FixFloorplanBillStatus::class,
        FixEmptyManufacturerUnitSale::class,
        GetTextrailParts::class,
        ResetInteractionMessages::class,
        ReimportInteractionMessages::class,
        RemoveBrokenCharacters::class,
        MyScheduleWorkCommand::class,
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
                ->hourly()
                ->runInBackground();

        $schedule->command('crm:dms:update-po-num-ref')
                ->daily()
                ->runInBackground();

        //$schedule->command('leads:assign:hotpotato')->withoutOverlapping();

        $schedule->command('leads:import:adf')
                ->everyFiveMinutes()
                ->runInBackground();


        /**
         * Campaigns/Blasts
         */
        $schedule->command('text:process-campaign')
                ->withoutOverlapping()
                ->runInBackground();

        $schedule->command('text:deliver-blast')
                ->withoutOverlapping()
                ->runInBackground();

        $schedule->command('text:auto-expire-phones')
                ->weeklyOn(7, '4:00')
                ->runInBackground();



        /**
         * Scrape Replies
         */
        // 0 - 2999
        $schedule->command('email:scrape-replies 0 2999')
                ->withoutOverlapping()
                ->runInBackground();

        // 3000 - 5999
        $schedule->command('email:scrape-replies 3000 5999')
                ->withoutOverlapping()
                ->runInBackground();

        // 6000 - 6499
        $schedule->command('email:scrape-replies 6000 6499')
                ->withoutOverlapping()
                ->runInBackground();

        // 6500 - 8999
        $schedule->command('email:scrape-replies 6500 8999')
                ->withoutOverlapping()
                ->runInBackground();

        // 9000+
        $schedule->command('email:scrape-replies 9000')
                ->withoutOverlapping()
                ->runInBackground();

        $schedule->command('files:clear-local-tmp-folder')
            ->weeklyOn(7, '4:00')
            ->runInBackground();

        $schedule->command('website:generate-dealer-specific-site-urls')
            ->daily()
            ->runInBackground();

        $schedule->command('inventory:auto-archive-sold-items')
            ->daily()
            ->runInBackground();

        $schedule->command('inventory:fix-floorplan-bill-status')
            ->hourly()
            ->runInBackground();


        /**
         * Scrape Facebook Messages
         */
        $schedule->command('facebook:scrape-messages')
                ->withoutOverlapping()
                ->runInBackground();

        // $schedule->command('inspire')
        //          ->hourly();

        /**
         * Import textrail parts
         */

        $schedule->command('command:get-textrail-parts')
           ->dailyAt('1:00')
           ->runInBackground();
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
