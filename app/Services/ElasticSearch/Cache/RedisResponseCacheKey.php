<?php

namespace App\Services\ElasticSearch\Cache;

use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;
use Illuminate\Support\Str;

class RedisResponseCacheKey implements ResponseCacheKeyInterface
{
    private const SEPARATOR = '_';

    public const CLEAR_ALL_PATTERN = 'inventories.*';

    public const SINGLE_PATTERN = 'inventories.single';

    /**
     * It returns a string like `inventories.search.0698854bbb02f1f9dcd91350272e6e4f42150150.dealers:_4203_.inventories:_3207402_3207708_3207709_3207815_3207283_`
     */
    public function collection(string $requestId, ElasticSearchQueryResult $result): string
    {
        $inventories = collect([]);
        $dealers = collect([]);

        foreach ($result->hints as $hint) {
            $inventories->push($hint->_source->id);
            $dealers->push($hint->_source->dealerId);
        }

        return sprintf(
            'inventories.search.%s.dealers:_%s_.inventories:_%s_',
            $requestId, $dealers->unique()->join(self::SEPARATOR), $inventories->join(self::SEPARATOR)
        );
    }

    /**
     * It returns a string like `inventories.single.323332.dealer:4203`
     */
    public function single($inventoryId, $dealerId): string
    {
        return sprintf('inventories.single.%d.dealer:%d', $inventoryId, $dealerId);
    }

    /**
     * It returns a string like `inventories.single.323332.dealer:4203`
     */
    public function deleteSingle(int $inventoryId, int $dealerId): string
    {
        return $this->single($inventoryId, $dealerId);
    }

    /**
     * It returns a string like `inventories.search.*.dealers:*.inventories:*_323332_*`
     *
     * @param int $inventoryId
     * @return string
     */
    public function deleteSingleFromCollection(int $inventoryId): string
    {
        return sprintf('inventories.search.*.dealers:*.inventories:*_%d_*', $inventoryId);
    }

    /**
     * It returns a string like `inventories.search.*.dealers:*_4203_*.inventories:*`
     */
    public function deleteByDealer(int $dealerId): string
    {
        return sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $dealerId);
    }

    /**
     * It returns a string like `inventories.single.*.dealer:4203`
     */
    public function deleteSingleByDealer(int $dealerId): string
    {
        return sprintf('inventories.single.*.dealer:%d', $dealerId);
    }

    public function isSingleKey(string $key): bool
    {
        return Str::contains($key, self::SINGLE_PATTERN) || $key === self::CLEAR_ALL_PATTERN;
    }

    public function isSearchKey(string $key): bool
    {
        return !$this->isSingleKey($key) || $key === self::CLEAR_ALL_PATTERN;
    }
}
