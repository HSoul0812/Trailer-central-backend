<?php

namespace App\Console\Commands\QuickBooks;

use App\Models\CRM\Dms\Quickbooks\Account;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Models\User\User as Dealer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class DeleteQuickBooksAccountsAfterDate extends Command
{
    protected $signature = 'qb:delete-accounts-after-date
        {dealer_id : The dealer id to delete the QuickBooks account}
        {from : A date in YYYY-mm-dd format (i.e. 2021-11-30). Time is start of day.}
        {--to= : A date in YYYY-mm-dd format (i.e. 2021-12-31). Default is today (time is end of day).}
    ';

    protected $description = 'Delete QuickBooks account after the specified date.';

    public function handle()
    {
        $dealerId = $this->argument('dealer_id');
        $validDealerId = Dealer::where('dealer_id', $dealerId)->exists();

        if (! $validDealerId) {
            $this->error("Not found dealer id $dealerId.");
            return 1;
        }

        $from = Carbon::parse($this->argument('from'))->startOfDay();

        // For to, we read from the option first
        // If the option doesn't exist, the to will be the last second of today
        $to = !is_null($this->option('to'))
            ? Carbon::parse($this->option('to'))->endOfDay()
            : now()->endOfDay();

        if (strtolower($this->ask("Delete QuickBooks account that was created between $from and $to? (y/N or other)")) !== 'y') {
            return 0;
        }

        // Prepare the delete query but not executing it yet
        $query = QuickbookApproval::query()
            ->where('tb_name', (new Account())->getTable())
            ->where('dealer_id', $dealerId)
            ->where('is_approved', 0)
            ->whereBetween('created_at', [$from, $to]);

        // Just count the number of records
        $count = $query->count();

        if ($count === 0) {
            $this->info("No accounts to remove (found 0 accounts created between $from and $to of the dealer id $dealerId).");
            return 1;
        }

        if (strtolower($this->ask("$count accounts in total will be deleted, are you sure? (y/N or other)")) !== 'y') {
            return 0;
        }

        // Run the delete query for real
        $query->delete();

        $this->info("QuickBooks accounts that was created between $from and $to of the dealer id $dealerId have been deleted.");
        $this->info("Total number of account deleted: $count.");

        return 0;
    }
}
