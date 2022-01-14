<?php

namespace App\Console\Commands\CRM\Dms;

use App\Models\CRM\User\Customer;
use App\Models\User\User;
use Elasticsearch\Client;
use Illuminate\Console\Command;

class RemoveDeletedCustomersFromESIndex extends Command
{
    const PER_PAGE = 1000;

    protected $signature = 'crm:dms:remove-deleted-customers-from-es-index {dealer_id : The dealer id to remove the deleted parts index}';

    protected $description = 'Remove the deleted customers index from ElasticSearch';

    public function handle(Client $esClient)
    {
        $dealerId = $this->argument('dealer_id');

        $dealerExist = User::where('dealer_id', $dealerId)->exists();
        if (!$dealerExist) {
            $this->error("Dealer id $dealerId doesn't exist.");
            return 1;
        }

        $page = 1;
        $totalDelete = 0;
        $customerIndex = (new Customer())->searchableAs();

        do {
            $search = Customer::boolSearch()
                ->mustRaw([
                    ['match_phrase' => ['dealer_id' => $dealerId]],
                ]);

            $search->from(($page - 1) * self::PER_PAGE);
            $search->size(self::PER_PAGE);

            $searchResult = $search->execute()->toArray();

            // Break from the loop if there is no more data
            if (empty($searchResult)) {
                break;
            }

            // Loop through each result and store the one that no longer has
            // a model on the database to an array
            $toRemoveDocumentIds = [];
            foreach ($searchResult as $result) {
                if ($result['model'] !== null) {
                    continue;
                }

                $toRemoveDocumentIds[] = $result['document']['id'];
            }

            // If on this page we don't have deleted customers, we'll move
            // to the next page
            if (empty($toRemoveDocumentIds)) {
                $page++;
                continue;
            }

            // Then, build each document id as a bulk delete operation array
            $body = array_map(function (string $documentID) {
                return [
                    'delete' => [
                        '_id' => $documentID,
                    ],
                ];
            }, $toRemoveDocumentIds);

            // Send an actual bulk delete command to WS
            $bulkResult = $esClient->bulk([
                'index' => $customerIndex,
                'body' => $body,
            ]);

            // Summarize the deletion
            foreach ($bulkResult['items'] as $result) {
                $data = $result['delete'];
                $totalDelete++;
                $this->info("Deleted customer id {$data['_id']} from ES index.");
            }

            $page++;
        } while (true);

        $this->info("The number of deleted index in ES: $totalDelete");
        $this->info("The command has finished.");

        return 0;
    }
}
