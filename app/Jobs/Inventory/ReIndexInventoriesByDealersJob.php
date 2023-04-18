<?php

namespace App\Jobs\Inventory;

use App\Contracts\LoggerServiceInterface;
use App\Jobs\Job;
use App\Models\BatchedJob;
use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

class ReIndexInventoriesByDealersJob extends Job
{
    /** @var int time in seconds */
    private const WAIT_TIME_IN_SECONDS = 15;

    /** @var string[] list of queues which are monitored */
    private const MONITORED_QUEUES = ['scout'];

    /** @var string  */
    private const MONITORED_GROUP = 'inventory-reindex-by-dealer';

    /**
     * @var array<integer>
     */
    private $dealerIds;

    public $queue = 'scout';

    /**  @var array|null */
    private $context;

    /** @var int The number of times the job may be attempted. */
    public $tries = 1;

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

        Job::batch(
            function (BatchedJob $batch): void {
                Inventory::makeAllSearchableByDealers($this->dealerIds);
            },
            self::MONITORED_QUEUES,
            self::MONITORED_GROUP.'-'.implode('-', $this->dealerIds),
            self::WAIT_TIME_IN_SECONDS,
            $this->context
        );

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
