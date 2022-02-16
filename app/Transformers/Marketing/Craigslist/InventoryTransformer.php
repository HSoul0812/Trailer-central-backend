<?php

namespace App\Transformers\Marketing\Craigslist;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryImage;
use App\Models\Inventory\Image;
use App\Transformers\User\UserTransformer;
use App\Transformers\User\DealerLocationTransformer;
use App\Transformers\Website\WebsiteTransformer;

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
            'id' => $inventory->inventory_id,
            'stock' => $inventory->stock,
            'title' => $inventory->title,
            'category' => $inventory->category,
            'manufacturer' => $inventory->manufacturer,
            'price' => $inventory->price,
            'status' => '',
            'primary_image' => !empty($inventory->primary_image) ?
                config('app.cdn_url') . $this->inventory->primary_image->image->filename : '',
            'last_posted' => '',
            'next_scheduled' => ''
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
