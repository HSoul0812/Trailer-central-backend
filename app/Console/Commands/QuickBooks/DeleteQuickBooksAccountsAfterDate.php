<?php

namespace App\Console\Commands\QuickBooks;

use App\Models\CRM\Dms\Quickbooks\Account;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteQuickBooksAccountsAfterDate extends Command
{
    protected $signature = 'qb:delete-accounts-after-date {date : Date in YYYY-mm-dd format (i.e. 2021-12-31)}';

    protected $description = 'Delete QuickBooks account after the specified date.';

    public function handle()
    {
        $from = Carbon::parse($this->argument('date'))->startOfDay();

        if (strtolower($this->ask("Delete QuickBooks account that was created on and after $from? (y/N or other)")) !== 'y') {
            return 0;
        }

        $count = Account::where('created_at', '>=', $from)->count();

        if (strtolower($this->ask("$count accounts in total will be deleted, are you sure? (y/N or other)")) !== 'y') {
            return 0;
        }

        // Run the delete query for real
        Account::where('created_at', '>=', $from)->delete();

        $this->info("QuickBooks accounts that was created on and after $from have been deleted.");
        $this->info("Total number of account deleted: $count.");
    }
}
