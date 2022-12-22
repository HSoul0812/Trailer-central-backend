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
     * @param $id
     * @return string
     */
    public function single($id): string;

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
}
