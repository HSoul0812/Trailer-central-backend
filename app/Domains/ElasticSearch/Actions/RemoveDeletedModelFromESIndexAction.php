<?php

namespace App\Domains\ElasticSearch\Actions;

use DB;
use Elasticsearch\Client;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\Builder;

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
     * The search_after property that will send to ES
     *
     * @var array
     */
    private $searchAfter = [];

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
     * @param Model|string $model
     * @return RemoveDeletedModelFromESIndexAction
     * @throws Exception
     */
    public function forModel($model): RemoveDeletedModelFromESIndexAction
    {
        // We accept both string and Model class for the $model
        // If they send in the model, we'll transform that to the
        // concrete class
        if (is_string($model)) {
            $this->model = new $model();
        } else {
            $this->model = $model;
        }

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
        $totalDelete = 0;

        do {
            $searchParams = $this->getSearchParams();

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
                $modelIds[] = (int) $hit['_id'];

                // At the same time, keep storing the last sort in the hits array
                // as the next search_after value
                $this->searchAfter = $hit['sort'];
            }

            $table = $this->model->getTable();
            $modelUsesSoftDelete = $this->model::hasGlobalScope(SoftDeletingScope::class);

            // For performance’s sake, we'll fetch all the model ids at once
            $query = DB::table($table)
                ->whereIn('id', $modelIds)
                ->when($modelUsesSoftDelete, function(Builder $query) {
                    $query->whereNull('deleted_at');
                });

            $modelIdsInDB = $query->pluck('id')->toArray();

            // Then, compare it with the full list of model ids, the missing ones
            // are the one that we need to delete
            $documentIdsToRemove = array_values(array_diff($modelIds, $modelIdsInDB));

            // No need to do anything in this loop if all the model are still exist
            // in the database
            if (empty($documentIdsToRemove)) {
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

    /**
     * Get the search param that we'll send to ES
     *
     * @return array
     */
    private function getSearchParams(): array
    {
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

        // We'll use the Search After API to continue searching the data
        // in the next pages
        // Ref: https://www.elastic.co/guide/en/elasticsearch/reference/current/paginate-search-results.html
        if (!empty($this->searchAfter)) {
            $searchParams['body']['search_after'] = $this->searchAfter;
        }

        return $searchParams;
    }
}
