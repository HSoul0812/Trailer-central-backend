<?php

namespace App\Services\ElasticSearch\Cache;

use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;

interface ResponseCacheKeyInterface
{
    /**
     * @param string $requestId
     * @param ElasticSearchQueryResult $result
     * @return string
     */
    public function collection(string $requestId, ElasticSearchQueryResult $result): string;

    /**
     * @param $inventoryId
     * @param $dealerId
     * @return string
     */
    public function single($inventoryId, $dealerId): string;

    /**
     * @param int $id
     * @return string
     */
    public function deleteSingle(int $id): string;

    /**
     * @param int $id
     * @return string
     */
    public function deleteSingleFromCollection(int $id): string;

    /**
     * @param int $id
     * @return string
     */
    public function deleteByDealer(int $id): string;

    /**
     * @param int $id
     * @return string
     */
    public function deleteSingleByDealer(int $id): string;
}
