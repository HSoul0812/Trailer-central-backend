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
use App\Models\User\User as Dealer;
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
     * Get a new query to restore one or more models by their queueable IDs.
     *
     * @param  array|int  $ids
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQueryForRestoration($ids)
    {
        return is_array($ids)
            ? $this->newQueryWithoutScopes()->with('user', 'user.website', 'dealerLocation')->whereIn($this->getQualifiedKeyName(), $ids)
            : $this->newQueryWithoutScopes()->with('user', 'user.website', 'dealerLocation')->whereKey($ids);
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
        self::query()->select('inventory.inventory_id')
            ->whereIn('dealer_id', $dealers)
            ->searchable();
    }

    public static function makeAllSearchableByDealerLocationId(int $dealerLocationId): void
    {
        self::query()->select('inventory.inventory_id')
            ->where('dealer_location_id', $dealerLocationId)
            ->searchable();
    }

    /**
     * It will iterate over all dealers, then over all inventories which belongs to the dealer
     *
     * @return void
     */
    public static function makeAllSearchable(): void
    {
        Dealer::query()->select('dealer.dealer_id')
            ->get()
            ->each(function (Dealer $dealer): void {
                self::query()->select('inventory.inventory_id')
                    ->where('inventory.dealer_id', $dealer->dealer_id)
                    ->searchable();
            });
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

    /**
     * To avoid to dispatch jobs for image overlay generation, ElasticSearch indexation and invalidation cache
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function withoutImageOverlayGenerationSearchSyncingAndCacheInvalidation(callable $callback)
    {
        $isCacheInvalidationEnabled = self::isCacheInvalidationEnabled();
        $isSearchSyncingEnabled = self::isSearchSyncingEnabled();
        $isImageOverlayGenerationEnabled = self::isOverlayGenerationEnabled();

        self::disableCacheInvalidationAndSearchSyncing();

        try {
            return $callback();
        } finally {
            if ($isImageOverlayGenerationEnabled) {
                self::enableOverlayGeneration();
            }

            if ($isCacheInvalidationEnabled) {
                self::enableCacheInvalidation();
            }

            if ($isSearchSyncingEnabled) {
                self::enableSearchSyncing();
            }
        }
    }

    public static function disableImageOverlayGenerationCacheInvalidationAndSearchSyncing(): void
    {
        self::disableSearchSyncing();
        InventoryObserver::disableCacheInvalidation();
        self::disableOverlayGeneration();
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

    public static function enableOverlayGeneration(): void
    {
        self::$isOverlayGenerationEnabled = true;
    }

    public static function disableOverlayGeneration():void
    {
        self::$isOverlayGenerationEnabled = false;
    }

    public static function isOverlayGenerationEnabled(): bool
    {
        return self::$isOverlayGenerationEnabled;
    }

    public static function isCacheEnabledByFeatureFlag(): bool
    {
        return app(FeatureFlagRepositoryInterface::class)->isEnabled('inventory-sdk-cache');
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

        dispatch((new MakeSearchable($models, $this->searchableAs()))
            ->onQueue($models->first()->syncWithSearchUsingQueue())
            ->onConnection($models->first()->syncWithSearchUsing()));
    }
}
