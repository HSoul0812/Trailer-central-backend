<?php

namespace App\Transformers\Marketing\Craigslist;

use League\Fractal\TransformerAbstract;
use App\Services\Marketing\Craigslist\DTOs\ClappInventory;

/**
 * Class InventoryTransformer
 *
 * Inventory transformer also including craigslist post data
 *
 * @package App\Transformers\Marketing\Craigslist
 */
class InventoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'activePosts',
    ];

    public function transform(ClappInventory $inventory)
    {
        $return = [
            'identifier' => $inventory->inventoryId,
            'location_id' => $inventory->dealerLocationId,
            'stock' => $inventory->stock,
            'title' => $inventory->title,
            'category' => $inventory->category,
            'manufacturer' => $inventory->manufacturer,
            'price' => $inventory->price,
            'status' => $inventory->status,
            'primary_image' => $inventory->getPrimaryImage(),
            'last_posted' => $inventory->lastPosted,
            'next_scheduled' => $inventory->nextScheduled,
            'queue_id' => $inventory->queueId,
            'clid' => $inventory->craigslistId,
            'links' => []
        ];

        // View URL Exists?
        if($inventory->viewUrl) {
            $return['links'][] = ['url' => $inventory->viewUrl, 'type' => 'view'];
        }

        // Manage URL Exists?
        if($inventory->manageUrl) {
            $return['links'][] = ['url' => $inventory->manageUrl, 'type' => 'manage'];
        }

        // Is Scheduler?
        if($inventory->isScheduler()) {
            unset($return['status']);
        } else {
            unset($return['next_scheduled']);
            unset($return['queue_id']);
        }

        // Return Result
        return $return;
    }

}
