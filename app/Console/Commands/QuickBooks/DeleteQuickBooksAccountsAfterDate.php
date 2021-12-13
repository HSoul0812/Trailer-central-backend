<?php

namespace App\Console\Commands\QuickBooks;

use App\Models\CRM\Dms\Quickbooks\Account;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Models\User\User as Dealer;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteQuickBooksAccountsAfterDate extends Command
{
    protected $signature = 'qb:delete-accounts-after-date {dealer_id : The dealer id to delete the QuickBooks account} {date : Date in YYYY-mm-dd format (i.e. 2021-12-31)}';

    protected $description = 'Delete QuickBooks account after the specified date.';

    public function handle()
    {
        $dealerId = $this->argument('dealer_id');
        $validDealerId = Dealer::where('dealer_id', $dealerId)->exists();

        if (! $validDealerId) {
            $this->error("Not found dealer id $dealerId.");
            return 1;
        }

        $from = Carbon::parse($this->argument('date'))->startOfDay();

        if (strtolower($this->ask("Delete QuickBooks account that was created on and after $from? (y/N or other)")) !== 'y') {
            return 0;
        }

        // Prepare the delete query but not executing it yet
        $query = QuickbookApproval::query()
            ->where('dealer_id', $dealerId)
            ->where('tb_name', (new Account())->getTable())
            ->where('created_at', '>=', $from)
            ->whereNull('qb_id');

        // Just count the number of records
        $count = $query->count();

        if ($count === 0) {
            $this->info("No accounts to remove (found 0 accounts created after $from with dealer id $dealerId).");
            return 1;
        }

        if (strtolower($this->ask("$count accounts in total will be deleted, are you sure? (y/N or other)")) !== 'y') {
            return 0;
        }

        // Run the delete query for real
        $query->delete();

        $this->info("QuickBooks accounts that was created on and after $from of the dealer id $dealerId have been deleted.");
        $this->info("Total number of account deleted: $count.");

        return 0;
    }
}
