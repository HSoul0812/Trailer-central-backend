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
            'id' => $inventory->inventoryId,
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
            'craigslist_id' => $inventory->craigslistId,
            'view_url' => $inventory->viewUrl,
            'manage_url' => $inventory->manageUrl
        ];

        // Is Scheduler?
        if($inventory->isScheduler) {
            unset($return['status']);
        } else {
            unset($return['next_scheduled']);
            unset($return['queue_id']);
        }
    }

}
