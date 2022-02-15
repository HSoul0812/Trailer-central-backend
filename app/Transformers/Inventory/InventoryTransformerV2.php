<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\Inventory;
use App\Transformers\User\UserTransformer;
use App\Transformers\User\DealerLocationTransformer;
use App\Transformers\Website\WebsiteTransformer;

/**
 * Class InventoryTransformerV2
 *
 * Alternate inventory transformer where includes are not automatically loaded by default (performance issues)
 *
 * @package App\Transformers\Inventory
 */
class InventoryTransformerV2 extends TransformerAbstract
{
    protected $availableIncludes = [
        'website', 'user', 'dealerLocation', 'images',
    ];

    public function transform(Inventory $inventory)
    {
        return [
            'id' => $inventory->inventory_id,
            'active' => $inventory->active,
            'archived_at' => $inventory->archived_at,
            'availability' => $inventory->availability,
            'bill_id' => $inventory->bill_id,
            'brand' => $inventory->brand,
            'category' => $inventory->category,
            'condition' => $inventory->condition,
            'created_at' => $inventory->created_at,
            'dealer_location_id' => $inventory->dealer_location_id,
            'description' => $inventory->description,
            'entity_type_id' => $inventory->entity_type_id,
            'fp_balance' => $inventory->fp_balance,
            'fp_interest_paid' => $inventory->interest_paid,
            'fp_committed' => $inventory->fp_committed,
            'gvwr' => $inventory->gvwr,
            'height' => $inventory->height,
            'is_archived' => $inventory->is_archived,
            'is_floorplan_bill' => $inventory->is_floorplan_bill,
            'length' => $inventory->length,
            'manufacturer' => $inventory->manufacturer,
            'model' => $inventory->model,
            'msrp' => $inventory->msrp,
            'non_serialized' => $inventory->non_serialized,
            'note' => $inventory->note,
            'price' => $inventory->price,
            'sales_price' => $inventory->sales_price,
            'send_to_quickbooks' => $inventory->send_to_quickbooks,
            'status' => $inventory->status_label,
            'stock' => $inventory->stock,
            'title' => $inventory->title,
            'true_cost' => $inventory->true_cost,
            'cost_of_unit' => $inventory->cost_of_unit,
            'cost_of_shipping' => $inventory->cost_of_shipping,
            'cost_of_prep' => $inventory->cost_of_prep,
            'total_of_cost' => $inventory->total_of_cost,
            'video_embed_code' => $inventory->video_embed_code,
            'vin' => $inventory->vin,
            'weight' => $inventory->weight,
            'width' => $inventory->width,
            'year' => $inventory->year,
            'url' => $inventory->getUrl(),
        ];
    }

    public function includeWebsite($inventory)
    {
        return $this->item($inventory->user->website, new WebsiteTransformer);
    }

    public function includeUser($inventory)
    {
        return $this->item($inventory->user, new UserTransformer);
    }

    public function includeDealerLocation($inventory)
    {
        return $this->item($inventory->dealerLocation, new DealerLocationTransformer);
    }

    public function includeImages($inventory)
    {
        return $this->collection($inventory->images, new ImageTransformer);
    }

}
