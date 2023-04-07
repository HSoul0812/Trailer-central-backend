<?php

namespace App\Console\Commands\UserTracking;

use App\Console\Commands\Report\ReportInventoryViewAndImpressionCommand;
use Illuminate\Console\Command;

class ProcessUserTrackingsCommand extends Command
{
    protected $signature = 'user-tracking:process';

    protected $description = 'Process all the user tracking commands.';

    public function handle(): int
    {
        /**
         * The order of command to run is very important, we need to make sure that we populate
         * any missing information (like user location) before we start processing the reports
         */

        $this->call(PopulateUserLocationCommand::class);

        $this->call(PopulateMissingWebsiteUserIdCommand::class, [
            'date' => now()->subDay()->format(PopulateMissingWebsiteUserIdCommand::DATE_FORMAT),
        ]);

        $this->call(ReportInventoryViewAndImpressionCommand::class, [
            'date' => now()->subDay()->format(ReportInventoryViewAndImpressionCommand::DATE_FORMAT),
        ]);

        return 0;
    }
}
