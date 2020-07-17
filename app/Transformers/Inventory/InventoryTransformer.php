<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\Inventory;
use App\Transformers\User\UserTransformer;
use App\Transformers\User\DealerLocationTransformer;
use App\Transformers\Inventory\ImageTransformer;
use Illuminate\Database\Eloquent\Collection;

class InventoryTransformer extends TransformerAbstract
{

    protected $userTransformer;

    protected $dealerLocationTransformer;

    protected $imageTransformer;

    public function __construct()
    {
        $this->userTransformer = new UserTransformer();
        $this->dealerLocationTransformer = new DealerLocationTransformer();
        $this->imageTransformer = new ImageTransformer();
    }

    public function transform(Inventory $inventory)
    {
	 return [
             'id' => $inventory->inventory_id,
             'active' => $inventory->active,
             'archived_at' => $inventory->archived_at,
             'availability' => $inventory->availability,
             'bill_id' => $inventory->bill_id,
             'category' => $inventory->category,
             'condition' => $inventory->condition,
             'created_at' => $inventory->created_at,
             'dealer' => $this->userTransformer->transform($inventory->user),
             'dealer_location_id' => $inventory->dealer_location_id,
             'dealer_location' => $inventory->dealerLocation ? $this->dealerLocationTransformer->transform($inventory->dealerLocation) : null,
             'description' => $inventory->description,
             'entity_type_id' => $inventory->entity_type_id ,
             'fp_balance' => $inventory->fp_balance,
             'fp_interest_paid' => $inventory->fp_interest_paid,
             'fp_committed' => $inventory->fp_committed,
             'gvwr' => $inventory->gvwr,
             'height' => $inventory->height,
             'images' => $this->transformImages($inventory->images),
             'is_archived' => $inventory->is_archived,
             'is_floorplan_bill' => $inventory->is_floorplan_bill,
             'length' => $inventory->length,
             'manufacturer' => $inventory->manufacturer,
             'model' => $inventory->model,
             'msrp' => $inventory->msrp,
             'non_serialized' => $inventory->non_serialized,
             'note' => $inventory->note,
             'price' => $inventory->price,
             'send_to_quickbooks' => $inventory->send_to_quickbooks,
             'status' => $inventory->status,
             'stock' => $inventory->stock,
             'title' => $inventory->title,
             'true_cost' => $inventory->true_cost,
             'video_embed_code' => $inventory->video_embed_code,
             'vin' => $inventory->vin,
             'weight' => $inventory->weight,
             'width' => $inventory->width,
             'year' => $inventory->year,
             'color' => $inventory->color
         ];
    }

    private function transformImages(Collection $images)
    {
        $ret = [];
        foreach($images as $img) {
            $ret[] = $this->imageTransformer->transform($img);
        }
        return $ret;
    }
}
