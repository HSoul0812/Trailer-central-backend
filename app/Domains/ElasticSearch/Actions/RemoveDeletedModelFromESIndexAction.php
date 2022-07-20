<?php

namespace App\Domains\ElasticSearch\Actions;

use ElasticScoutDriverPlus\Builders\SearchRequestBuilder;
use Elasticsearch\Client;

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
    private $perPage = 1000;

    /**
     * The model that we want to remove the deleted data from ES index
     * @var
     */
    private $model;

    /**
     * The must raw criteria array
     * @var array
     */
    private $mustRaw = [];

    /**
     * The ES index
     *
     * @var string
     */
    private $index = '';

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
     * Set the model to process
     *
     * @param string $model
     * @return $this
     */
    public function withModel(string $model): RemoveDeletedModelFromESIndexAction
    {
        $this->model = $model;
        $this->index = (new $model)->searchableAs();

        return $this;
    }

    /**
     * Set the Must Raw criteria
     *
     * @param array $mustRaw
     * @return $this
     */
    public function withMustRaw(array $mustRaw): RemoveDeletedModelFromESIndexAction
    {
        $this->mustRaw = $mustRaw;

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
     * @param int $perPage
     * @return $this
     */
    public function perPage(int $perPage): RemoveDeletedModelFromESIndexAction
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * The main method, execute this action
     *
     * @return int[]
     */
    public function execute(): array
    {
        // Initialize some variables
        $page = 1;
        $totalDelete = 0;
        $search = $this->getSearchBuilder();

        // We will start looping from the first page to the page
        // that doesn't have data anymore
        do {
            $search->from(($page - 1) * $this->perPage);
            $search->size($this->perPage);

            $searchResult = $search->execute()->toArray();

            // Break from the loop if there is no more data
            if (empty($searchResult)) {
                break;
            }

            // Loop through each result and store the one that no longer has
            // a model on the database to an array
            $toRemoveDocumentIds = $this->getToRemoveDocumentIds($searchResult);

            // If on this page we don't have deleted model, we'll move
            // to the next page
            if (empty($toRemoveDocumentIds)) {
                $page++;
                continue;
            }

            // Then, build each document id as a bulk delete operation array
            $body = $this->getESBulkDeleteBody($toRemoveDocumentIds);

            // Send an actual bulk delete command to WS
            $bulkResult = $this->sendBulkDeleteRequestToES($body);

            // Summarize the deletion
            foreach ($bulkResult['items'] as $result) {
                $totalDelete++;
                call_user_func($this->onDeletedDocumentIdCallback, $result['delete']['_id']);
            }

            $page++;
        } while (true);

        // We return as an array for flexibility
        // in the future we can extend this class by adding
        // new fields here
        return [
            'total_delete' => $totalDelete,
        ];
    }

    /**
     * Get the ES search builder
     * @return SearchRequestBuilder
     */
    private function getSearchBuilder(): SearchRequestBuilder
    {
        $search = $this->model::boolSearch();

        if (!empty($this->mustRaw)) {
            $search->mustRaw($this->mustRaw);
        }

        return $search;
    }

    /**
     * Get the document ids to remove
     *
     * @param array $searchResult The ES search result
     * @return string[]
     */
    private function getToRemoveDocumentIds(array $searchResult): array
    {
        $toRemoveDocumentIds = [];

        foreach ($searchResult as $result) {
            // We want to keep only the result that has model record
            // AND that model record need to have deleted_at = null
            // we'll use data_get here so if the model doesn't have soft-delete
            // then it'd still work, basically no need to check for array
            // key existence
            if ($result['model'] !== null && data_get($result, 'model.deleted_at') === null) {
                continue;
            }

            $toRemoveDocumentIds[] = $result['document']['id'];
        }

        return $toRemoveDocumentIds;
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
