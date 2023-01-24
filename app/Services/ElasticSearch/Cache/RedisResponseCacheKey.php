<?php

namespace App\Services\ElasticSearch\Cache;

use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;
use Illuminate\Support\Str;

class RedisResponseCacheKey implements ResponseCacheKeyInterface
{
    private const SEPARATOR = '_';

    /**
     * @param string $requestId
     * @param ElasticSearchQueryResult $result
     * @return string
     */
    public function collection(string $requestId, ElasticSearchQueryResult $result): string
    {
        $inventories = collect([]);
        $dealers = collect([]);

        foreach ($result->hints as $hint) {
            $inventories->push($hint->_source->id);
            $dealers->push($hint->_source->dealerId);
        }

        $dealers = $dealers->unique()->map(function ($dealer) {
            return 'dealer:' . $dealer;
        })->join(self::SEPARATOR);

        $inventories = $inventories->join(self::SEPARATOR);

        return sprintf('inventories.%s_%s_%s_', $requestId, $dealers, $inventories);
    }

    /**
     * @param $inventoryId
     * @param $dealerId
     * @return string
     */
    public function single($inventoryId, $dealerId): string
    {
        return sprintf('inventories.single:%d:-dealer:%d', $inventoryId, $dealerId);
    }

    /**
     * @param  int  $id
     * @param  int  $dealerId
     * @return string
     */
    public function deleteSingle(int $id, int $dealerId): string
    {
        return sprintf('*inventories.single:%d:-dealer:%d', $id, $dealerId);
    }

    /**
     * @param int $id
     * @return string
     */
    public function deleteSingleFromCollection(int $id): string
    {
        return sprintf('*inventories.*_%d_*', $id);
    }

    /**
     * @param int $id
     * @return string
     */
    public function deleteByDealer(int $id): string
    {
        return sprintf('*inventories.*_dealer:%d_*', $id);
    }

    /**
     * @param int $id
     * @return string
     */
    public function deleteSingleByDealer(int $id): string
    {
        return sprintf('*inventories.single:*:-dealer:%d', $id);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isSingleKey(string $key): bool
    {
        return Str::contains($key, 'inventories.single');
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isSearchKey(string $key): bool
    {
        return !$this->isSingleKey($key);
    }
}
