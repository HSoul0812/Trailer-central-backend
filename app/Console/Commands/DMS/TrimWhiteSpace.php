<?php

namespace App\Console\Commands\DMS;

use App\Helpers\StringHelper;
use App\Models\CRM\User\Customer;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Class TrimWhiteSpace
 *
 * @package App\Console\Commands\DMS
 */
class TrimWhiteSpace extends Command
{
    /**
     * @inheritDoc
     */
    protected $signature = 'dms:trim-whitespace';

    /**
     * @inheritDoc
     */
    protected $description = 'Trim Whitespace from table fields.';

    public function handle()
    {
        $this->trimCustomers();
        dd('Hello, World');
        /*$dealerId = $this->argument('dealer_id');
        $validDealerId = Dealer::where('dealer_id', $dealerId)->exists();

        if (!$validDealerId) {
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

        return 0;*/
    }

    public function trimCustomers()
    {
        Schema::disableForeignKeyConstraints();

        $query = Customer::query()
        ->where('id', 61);

        $query->chunk(100, function ($models) {
            foreach ($models as $model) {
                try {
                    $model->first_name = StringHelper::trimWhiteSpaces($model->first_name);
                    $model->last_name = StringHelper::trimWhiteSpaces($model->last_name);
                    $model->display_name = StringHelper::trimWhiteSpaces($model->display_name);
                    $model->middle_name = StringHelper::trimWhiteSpaces($model->middle_name);
                    $model->company_name = StringHelper::trimWhiteSpaces($model->company_name);

                    $model->save();
                    // dd($model);
                } catch (Exception $exception) {
                    // Log::channel('scriptlog')
                    //     ->debug($exception->getMessage());
                    dd($exception->getMessage());
                }
            }
        });
    }
}
