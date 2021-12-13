<?php

namespace App\Console\Commands\QuickBooks;

use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteQuickBooksAccountsAfterDate extends Command
{
    protected $signature = 'qb:delete-accounts-after-date {date : Date in YYYY-mm-dd format (i.e. 2021-12-31)}';

    protected $description = 'Delete QuickBooks account after the specified date.';

    public function handle()
    {
        $from = Carbon::parse($this->argument('date'))->startOfDay();

        $confirm = $this->ask("Delete QuickBooks account that was created on and after $from? (y/N or other)");

        if (is_null($confirm)) {
            return 0;
        }

        if (strtolower($confirm) !== 'y') {
            return 0;
        }

        $this->info("Start!");
    }
}
