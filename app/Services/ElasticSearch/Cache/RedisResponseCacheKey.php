<?php

namespace App\Services\ElasticSearch\Cache;

use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;

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
     * @param int $id
     * @return string
     */
    public function deleteSingle(int $id): string
    {
        return sprintf('*inventories.single:%d*', $id);
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

    public static function humanReadable(string $pattern): string
    {
        if ($pattern === '*inventories.*') {
            return 'delete-everything';
        }

        if (preg_match('/\*inventories\.single:\d+.*$/s', $pattern)) {
            return 'delete-single';
        }

        if (preg_match('/\*inventories\..*_\d*_.*$/s', $pattern)) {
            return 'delete-single-from-collection';
        }

        if (preg_match('/\*inventories\..*_dealer:\d*_.*$/s', $pattern)) {
            return 'delete-by-dealer';
        }

        if (preg_match('/\*inventories\.single:\*:-dealer:\d.*$/s', $pattern)) {
            return 'delete-single-by-dealer';
        }

        return 'unknown-pattern';
    }
}
