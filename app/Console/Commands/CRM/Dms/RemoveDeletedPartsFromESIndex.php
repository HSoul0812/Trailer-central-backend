<?php

namespace App\Console\Commands\CRM\Dms;

use App\Domains\ElasticSearch\Actions\RemoveDeletedModelFromESIndexAction;
use App\Models\Parts\Part;
use App\Models\User\User;
use Illuminate\Console\Command;

class RemoveDeletedPartsFromESIndex extends Command
{
    protected $signature = 'crm:dms:remove-deleted-parts-from-es-index {dealer_id : The dealer id to remove the deleted parts index}';

    protected $description = 'Remove the deleted part models from the ElasticSearch index';

    public function handle(RemoveDeletedModelFromESIndexAction $action)
    {
        $dealerId = $this->argument('dealer_id');

        // Check for the dealer existence before start working on the main logic
        $dealerExist = User::where('dealer_id', $dealerId)->exists();
        if (!$dealerExist) {
            $this->error("Dealer id $dealerId doesn't exist.");

            return 1;
        }

        // Use the action class to do the heavy lifting
        $result = $action
            ->forModel(Part::class)
            ->fromDealerId($dealerId)
            ->withSize(3)
            ->withOnDeletedDocumentIdCallback(function (string $partId) {
                $this->info("Deleted part id $partId from ES index.");
            })
            ->execute();

        $this->info("The number of deleted index in ES: {$result['total_delete']}");
        $this->info('The command has finished.');

        return 0;
    }
}
