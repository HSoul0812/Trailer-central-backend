<?php

namespace App\Indexers\Inventory;

use App\Indexers\Searchable;
use App\Indexers\WithIndexConfigurator;
use App\Observers\Inventory\InventoryObserver;
use Exception;

/**
 * @method \Illuminate\Database\Eloquent\Builder query
 */
trait InventorySearchable
{
    use Searchable, WithIndexConfigurator;

    public function searchableAs(): string
    {
        return self::$searchableAs ?? $this->indexConfigurator()->aliasName();
    }

    public function toSearchableArray(): array
    {
        return $this->indexConfigurator()->transformer()->transform($this);
    }

    public function indexConfigurator(): InventoryElasticSearchConfigurator
    {
        if (self::$indexConfigurator) {
            return self::$indexConfigurator;
        }

        self::$indexConfigurator = new InventoryElasticSearchConfigurator();

        return self::$indexConfigurator;
    }

    /**
     * @throws Exception when some unknown error has been thrown
     */
    public static function makeAllSearchableUsingAliasStrategy(): void
    {
        $indexer = app(SafeIndexer::class);
        $indexer->ingest();
    }

    public static function makeAllSearchableByDealers(array $dealers = []): void
    {
        self::query()->whereIn('dealer_id', $dealers)
            ->orderBy('updated_at_auto', 'DESC')
            ->searchable();
    }

    public static function makeAllSearchableByDealerLocations(array $locations = []): void
    {
        self::query()->whereIn('dealer_location_id', $locations)
            ->orderBy('updated_at_auto', 'DESC')
            ->searchable();
    }

    public static function makeAllSearchable(): void
    {
        self::query()->orderBy('updated_at_auto', 'DESC')->searchable();
    }

    /**
     * Save without triggering the model events
     *
     * @param  array  $options
     * @return mixed
     */
    public function saveQuietly(array $options = [])
    {
        return static::withoutEvents(function () use ($options) {
            return $this->save($options);
        });
    }

    /**
     * Delete without triggering the model events
     *
     * @return mixed
     * @throws Exception
     */
    public function deleteQuietly()
    {
        return static::withoutEvents(function () {
            return $this->delete();
        });
    }

    /**
     * To avoid to dispatch jobs for invalidation cache and ElasticSearch indexation
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function withoutCacheInvalidationAndSearchSyncing(callable $callback)
    {
        $config = config('cache.inventory');

        self::disableCacheInvalidationAndSearchSyncing();

        try {
            return $callback();
        } finally {
            if ($config) {
                self::enableCacheInvalidation();
            }

            self::enableSearchSyncing();
        }
    }

    public static function disableCacheInvalidationAndSearchSyncing(): void
    {
        self::disableSearchSyncing();
        InventoryObserver::disableCacheInvalidation();
    }

    public static function enableCacheInvalidationAndSearchSyncing(): void
    {
        self::enableSearchSyncing();
        InventoryObserver::enableCacheInvalidation();
    }

    /**
     * To avoid to dispatch jobs for invalidation cache
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function withoutCacheInvalidation(callable $callback)
    {
        $config = config('cache.inventory');

        self::disableCacheInvalidation();

        try {
            return $callback();
        } finally {
            if ($config) {
                self::enableCacheInvalidation();
            }
        }
    }

    public static function disableCacheInvalidation(): void
    {
        InventoryObserver::disableCacheInvalidation();
    }

    public static function enableCacheInvalidation(): void
    {
        InventoryObserver::enableCacheInvalidation();
    }

    public static function isCacheInvalidationEnabled(): bool
    {
        return InventoryObserver::isCacheInvalidationEnabled();
    }
}
