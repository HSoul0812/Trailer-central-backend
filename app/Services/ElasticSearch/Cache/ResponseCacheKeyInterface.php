<?php

namespace App\Services\ElasticSearch\Cache;

use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;

interface ResponseCacheKeyInterface
{
    public function collection(string $requestId, ElasticSearchQueryResult $result): string;

    public function single(int $inventoryId, int $dealerId): string;

    public function deleteSingle(int $inventoryId, int $dealerId): string;

    public function deleteSingleFromCollection(int $inventoryId): string;

    public function deleteByDealer(int $dealerId): string;

    public function deleteSingleByDealer(int $dealerId): string;

    public function isSingleKey(string $key): bool;

    public function isSearchKey(string $key): bool;
}
