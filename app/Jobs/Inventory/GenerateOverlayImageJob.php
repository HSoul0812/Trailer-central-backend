<?php

namespace App\Jobs\Inventory;

use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\Inventory\InventoryServiceInterface;
use App\Models\Inventory\Inventory;
use App\Traits\Horizon\WithTags;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Job;
use Exception;

/**
 * @todo this job should be renamed to `InventoryBackGroundWorkFlowJob` when safe, it is is handling 3 processes
 *       a) Generate overlay
 *       b) ElasticSearch indexation
 *       c) Inventory cache invalidation
 */
class GenerateOverlayImageJob extends Job
{
    use Dispatchable, SerializesModels, WithTags;

    /** @var string this queue is used to process any job dispatched on any inventory dashboard event */
    public const HIGH_PRIORITY_QUEUE = 'overlay-images';

    /** @var string when the jobs is using this queue it will not delay any operation performed through dashboard */
    public const LOW_PRIORITY_QUEUE = 'overlay-images-low';

    /** @var int The number of times the job may be attempted. */
    public $tries = 2;

    /** @var int */
    private $inventoryId;

    /**  @var bool */
    private $reindexAndInvalidateCache;

    /** @var string */
    public $queue = self::HIGH_PRIORITY_QUEUE;

    public function __construct(int $inventoryId, ?bool $reindexAndInvalidateCache = null)
    {
        $this->inventoryId = $inventoryId;
        $this->reindexAndInvalidateCache = $reindexAndInvalidateCache ?? true;
    }

    public function handle(InventoryServiceInterface $service, InventoryRepositoryInterface $repo)
    {
        $log = Log::channel('inventory-overlays');

        try {
            Inventory::withoutCacheInvalidationAndSearchSyncing(function () use ($service): void {
                $service->generateOverlaysByInventoryId($this->inventoryId);
            });

            $log->info(
                'Inventory Images with Overlay has been successfully generated',
                ['inventory_id' => $this->inventoryId]
            );
        } catch (Exception $exception) {
            $log->error($exception->getMessage());
            $log->error($exception->getTraceAsString());
        }

        if ($this->reindexAndInvalidateCache) {
            /** @var Inventory $inventory */
            $inventory = $repo->get(['id' => $this->inventoryId]);

            $log->info('it will dispatch jobs for sync to index and invalidate cache', [
                'inventory_id' => $inventory->inventory_id, 'dealer_id' => $inventory->dealer_id
            ]);

            $service->tryToIndexAndInvalidateCacheByInventory($inventory);
        }
    }
}
