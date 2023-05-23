<?php

namespace App\Console\Commands\UserTracking;

use App\Console\Commands\Report\GenerateMonthlyImpressionCountingsReportCommand;
use App\Console\Commands\Report\GenerateMonthlyInventoryTrackingDataReportCommand;
use Illuminate\Console\Command;

class ProcessMonthlyUserTrackingCommand extends Command
{
    protected $signature = 'user-tracking:process-monthly';

    protected $description = 'Process monthly user tracking.';

    public function handle(): int
    {
        $lastMonth = now()->subMonth()->startOfMonth();

        $this->call(GenerateMonthlyInventoryTrackingDataReportCommand::class, [
            'year' => $lastMonth->year,
            'month' => $lastMonth->month,
        ]);

        $this->call(GenerateMonthlyImpressionCountingsReportCommand::class, [
            'year' => $lastMonth->year,
            'month' => $lastMonth->month,
        ]);

        // TODO: Add a call to clear logs here.

        return 0;
    }
}
