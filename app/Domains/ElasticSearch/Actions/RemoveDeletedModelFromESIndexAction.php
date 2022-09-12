<?php

namespace App\Domains\ElasticSearch\Actions;

use DB;
use Elasticsearch\Client;
use Exception;
use Illuminate\Database\Eloquent\Model;

class RemoveDeletedModelFromESIndexAction
{
    /**
     * The ElasticSearch Client
     *
     * @var Client
     */
    private $esClient;

    /**
     * Number of record per page that we want to fetch
     *
     * @var int
     */
    private $size = 1000;

    /**
     * The Model that we want to remove the data
     *
     * @var Model
     */
    private $model;

    /**
     * The index that we derived from the model
     *
     * @var
     */
    private $index;

    /**
     * The Dealer ID
     * @var
     */
    private $dealerId;

    /**
     * The callback for when the document id is deleted
     * @var callback
     */
    private $onDeletedDocumentIdCallback;

    public function __construct(Client $client)
    {
        $this->esClient = $client;

        $this->onDeletedDocumentIdCallback = function (string $documentId) {
        };
    }

    /**
     * Set the ES index you want to delete the data from
     *
     * @param Model $model
     * @return RemoveDeletedModelFromESIndexAction
     * @throws Exception
     */
    public function forModel(Model $model): RemoveDeletedModelFromESIndexAction
    {
        $this->model = $model;

        if (!method_exists($this->model, 'searchableAs')) {
            throw new Exception("The method searchableAs doesn't exist in the model $this->model.");
        }

        $this->index = $this->model->searchableAs();

        return $this;
    }

    /**
     * Set the dealer id that we want to delete this data from
     *
     * @param int $dealerId
     * @return $this
     */
    public function fromDealerId(int $dealerId): RemoveDeletedModelFromESIndexAction
    {
        $this->dealerId = $dealerId;

        return $this;
    }

    /**
     * Set the deleted document id callable
     *
     * @param callable $callback
     * @return $this
     */
    public function withOnDeletedDocumentIdCallback(callable $callback): RemoveDeletedModelFromESIndexAction
    {
        $this->onDeletedDocumentIdCallback = $callback;

        return $this;
    }

    /**
     * Set the amount of record per page that we want to fetch
     *
     * @param int $size
     * @return $this
     */
    public function withSize(int $size): RemoveDeletedModelFromESIndexAction
    {
        $this->size = $size;

        return $this;
    }

    /**
     * The main method, execute this action
     *
     * @return int[]
     */
    public function execute(): array
    {
        // $client = ClientBuilder::create()
        //         ->setHosts(['storage.pond.dev.trailercentral.com:9201'])
        //         ->build();

        // $client->delete([
        //     'index' => 'parts',
        //     'id' => 306,
        // ]);
        //
        // dd([]);
        $totalDelete = 0;
        $searchAfter = [];

        do {
            $searchParams = [
                'index' => $this->index,
                'body' => [
                    'size' => $this->size,
                    'query' => [
                        'match' => [
                            'dealer_id' => $this->dealerId,
                        ],
                    ],
                    'sort' => [[
                        'id' => 'asc',
                    ]],
                ],
            ];

            if (!empty($searchAfter)) {
                $searchParams['body']['search_after'] = $searchAfter;
            }

            $response = $this->esClient->search($searchParams);

            // We only break this while loop when we search and no longer get
            // the result back from ES
            if (empty($response['hits']['hits'])) {
                break;
            }

            $modelIds = [];

            // Loop through each hit and if it doesn't exist in the database
            // add it in the id to remove
            foreach ($response['hits']['hits'] as $hit) {
                $modelIds[] = $hit['_source']['id'];

                // At the same time, keep storing the last sort in the hits array
                // as the next search_after value
                $searchAfter = $hit['sort'];
            }

            // For performance’s sake, we'll fetch all the model ids at once
            $modelIdsInDB = DB::table($this->model->getTable())
                ->whereIn('id', $modelIds)
                ->pluck('id')
                ->toArray();

            // Then, compare it with the full list of model ids, the missing ones
            // are the one that we need to delete
            $documentIdsToRemove = array_diff($modelIds, $modelIdsInDB);

            // No need to do anything in this loop if all the model are still exist
            // in the database
            if (!empty($documentIdsToRemove)) {
                continue;
            }

            // Prepare the bulk delete request body
            $body = $this->getESBulkDeleteBody($documentIdsToRemove);

            // Send the bulk delete message to ES
            $bulkResult = $this->sendBulkDeleteRequestToES($body);

            // Summarize the deletion
            foreach ($bulkResult['items'] as $result) {
                $totalDelete++;
                call_user_func($this->onDeletedDocumentIdCallback, $result['delete']['_id']);
            }
        } while (true);

        // We return as an array for flexibility
        // in the future we can extend this class by adding
        // new fields here
        return [
            'total_delete' => $totalDelete,
        ];
    }

    /**
     * Get the ES Bulk delete request body
     *
     * @param array $toRemoveDocumentIds
     * @return array
     */
    private function getESBulkDeleteBody(array $toRemoveDocumentIds): array
    {
        return array_map(function (string $documentID) {
            return [
                'delete' => [
                    '_id' => $documentID,
                ],
            ];
        }, $toRemoveDocumentIds);
    }

    /**
     * Send the actual bulk delete request to ES
     *
     * @param $body
     * @return array
     */
    private function sendBulkDeleteRequestToES($body): array
    {
        return $this->esClient->bulk([
            'index' => $this->index,
            'body' => $body,
        ]);
    }
}
