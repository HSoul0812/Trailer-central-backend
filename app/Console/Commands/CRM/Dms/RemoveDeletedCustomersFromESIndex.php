<?php

namespace App\Console\Commands\CRM\Dms;

use App\Domains\ElasticSearch\Actions\RemoveDeletedModelFromESIndexAction;
use App\Models\CRM\User\Customer;
use App\Models\User\User;
use Exception;
use Illuminate\Console\Command;

class RemoveDeletedCustomersFromESIndex extends Command
{
    /**
     * Command example:
     *
     * Call without specifying the size per page
     * php artisan crm:dms:remove-deleted-customers-from-es-index 1001
     *
     * Call with a specified size 500 per page
     * php artisan crm:dms:remove-deleted-customers-from-es-index 1001 500
     *
     * @var string
     */
    protected $signature = '
        crm:dms:remove-deleted-customers-from-es-index
        {dealer_id : The dealer id to remove the deleted parts index}
        {size=1000 : The size for each page that we loop through}
    ';

    protected $description = 'Remove the deleted customer models from the ElasticSearch index';

    /**
     * @param RemoveDeletedModelFromESIndexAction $action
     * @return int
     */
    public function handle(RemoveDeletedModelFromESIndexAction $action)
    {
        $dealerId = $this->argument('dealer_id');

        // Check for the dealer existence before start working on the main logic
        $dealerExist = User::where('dealer_id', (int) $dealerId)->exists();
        if (!$dealerExist) {
            $this->error("Dealer id $dealerId doesn't exist.");
            return 1;
        }

        // Use the action class to do the heavy lifting
        try {
            $result = $action
                ->forModel(new Customer())
                ->fromDealerId($dealerId)
                ->withSize((int) $this->argument('size'))
                ->withOnDeletedDocumentIdCallback(function (string $partId) {
                    $this->info("Deleted customer id $partId from ES index.");
                })
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $this->info("The number of deleted index in ES: {$result['total_delete']}");
        $this->info("The command has finished.");

        return 0;
    }
}
