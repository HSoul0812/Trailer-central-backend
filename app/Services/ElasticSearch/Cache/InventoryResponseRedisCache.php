<?php

namespace App\Services\ElasticSearch\Cache;

class InventoryResponseRedisCache implements InventoryResponseCacheInterface
{
    /** @var ResponseCacheInterface */
    private $searchCache;

    /** @var ResponseCacheInterface */
    private $singleCache;

    /** @var ResponseCacheKeyInterface */
    private $responseCacheKey;

    public function __construct(
        ResponseCacheKeyInterface $responseCacheKey,
        ResponseCacheInterface $searchCache,
        ResponseCacheInterface $singleCache
    ) {
        $this->responseCacheKey = $responseCacheKey;
        $this->searchCache = $searchCache;
        $this->singleCache = $singleCache;
    }

    /**
     * It should store the cache in the proper database
     *
     * @param  string  $key
     * @param  string  $value
     * @return void
     */
    public function set(string $key, string $value): void
    {
        if ($this->responseCacheKey->isSearchKey($key)) {
            $this->searchCache->set($key, $value);

            return;
        }

        $this->singleCache->set($key, $value);
    }

    public function forget(array $keyPatterns): void
    {
        ['search' => $searchKeyPatterns, 'single' => $singleKeyPatterns] = $this->sliceKeyPatterns($keyPatterns);

        if (count($searchKeyPatterns)) {
            $this->searchCache->forget(...$searchKeyPatterns);
        }

        if (count($singleKeyPatterns)) {
            $this->singleCache->forget(...$singleKeyPatterns);
        }
    }

    public function invalidate(array $keyPatterns): void
    {
        ['search' => $searchKeyPatterns, 'single' => $singleKeyPatterns] = $this->sliceKeyPatterns($keyPatterns);

        if (count($searchKeyPatterns)) {
            $this->searchCache->invalidate(...$searchKeyPatterns);
        }

        if (count($singleKeyPatterns)) {
            $this->singleCache->invalidate(...$singleKeyPatterns);
        }
    }

    /**
     * @param  array  $keyPatterns
     * @return array{search: array<string>, single: array<string>}
     */
    private function sliceKeyPatterns(array $keyPatterns): array
    {
        $collection = collect($keyPatterns);

        return [
            'search' => $collection->filter(function (string $key): bool {
                return $this->responseCacheKey->isSearchKey($key);
            })->values(),
            'single' => $collection->filter(function (string $key): bool {
                return $this->responseCacheKey->isSingleKey($key);
            })->values()
        ];
    }
}
