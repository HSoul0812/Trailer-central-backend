<?php

namespace App\Indexers\Inventory;

use App\Indexers\Searchable;
use App\Indexers\WithIndexConfigurator;
use App\Models\Inventory\Inventory;
use App\Observers\Inventory\InventoryObserver;
use App\Repositories\FeatureFlagRepositoryInterface;
use Exception;
use App\Jobs\Scout\MakeSearchable;
use Laravel\Scout\ModelObserver;

/**
 * @method \Illuminate\Database\Eloquent\Builder query
 */
trait InventorySearchable
{
    use Searchable, WithIndexConfigurator;

    public static function bootInventorySearchable(): void
    {
        $repo = app(FeatureFlagRepositoryInterface::class);

        if ($repo->isEnabled('inventory-sdk-cache')) {
            Inventory::enableCacheInvalidation();
        }
    }

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

    public static function makeAllSearchableByDealerLocationId(int $dealerLocationId): void
    {
        self::query()->where('dealer_location_id', $dealerLocationId)
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
        $isCacheInvalidationEnabled = self::isCacheInvalidationEnabled();
        $isSearchSyncingEnabled = self::isSearchSyncingEnabled();

        self::disableCacheInvalidationAndSearchSyncing();

        try {
            return $callback();
        } finally {
            if ($isCacheInvalidationEnabled) {
                self::enableCacheInvalidation();
            }

            if ($isSearchSyncingEnabled) {
                self::enableSearchSyncing();
            }
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
        $isCacheInvalidationEnabled = self::isCacheInvalidationEnabled();

        self::disableCacheInvalidation();

        try {
            return $callback();
        } finally {
            if ($isCacheInvalidationEnabled) {
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

    public static function isSearchSyncingEnabled(): bool
    {
        return !ModelObserver::syncingDisabledFor(__CLASS__);
    }

    /**
     * Dispatch the job to make the given models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function queueMakeSearchable($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        if (! config('scout.queue')) {
            return $models->first()->searchableUsing()->update($models);
        }

        dispatch((new MakeSearchable($models))
            ->onQueue($models->first()->syncWithSearchUsingQueue())
            ->onConnection($models->first()->syncWithSearchUsing()));
    }
}
