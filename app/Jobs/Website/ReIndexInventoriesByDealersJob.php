<?php

namespace App\Jobs\Website;

use App\Contracts\LoggerServiceInterface;
use App\Jobs\Job;
use App\Models\BatchedJob;
use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

class ReIndexInventoriesByDealersJob extends Job
{
    /** @var int time in seconds */
    private const WAIT_TIME = 15;

    /**
     * @var array<integer>
     */
    private $dealerIds;

    public $queue = 'scout';

    /**  @var array|null */
    private $context;

    public function __construct(array $dealerIds, ?array $context = null)
    {
        $this->dealerIds = $dealerIds;
        $this->context = $context ?? ['dealer_ids' => $dealerIds];
    }

    public function handle(
        InventoryResponseCacheInterface $responseCache,
        ResponseCacheKeyInterface $responseCacheKey,
        LoggerServiceInterface $logger
    ): void {
        $logger->info(
            'Enqueueing the job to reindex inventory by dealer ids',
            ['dealer_ids' => $this->dealerIds]
        );

        Job::batch(function (BatchedJob $batch): void {
            Inventory::makeAllSearchableByDealers($this->dealerIds);
        }, __CLASS__, self::WAIT_TIME, $this->context);

        $logger->info(
            'Enqueueing the job to invalidate cache by dealer ids',
            ['dealer_ids' => $this->dealerIds]
        );

        foreach ($this->dealerIds as $dealerId) {
            $responseCache->forget([
                $responseCacheKey->deleteByDealer($dealerId),
                $responseCacheKey->deleteSingleByDealer($dealerId)
            ]);
        }
    }
}
