<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\Inventory;
use App\Transformers\User\UserTransformer;
use App\Transformers\User\DealerLocationTransformer;
use App\Transformers\Inventory\ImageTransformer;
use Illuminate\Database\Eloquent\Collection;
use App\Transformers\Website\WebsiteTransformer;

class InventoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'website'
    ];

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
             'brand' => $inventory->brand,
             'category' => $inventory->category,
             'category_label' => $inventory->category_label,
             'condition' => $inventory->condition,             
             'dealer' => $this->userTransformer->transform($inventory->user),
             'dealer_location_id' => $inventory->dealer_location_id,
             'dealer_location' => $inventory->dealerLocation ? $this->dealerLocationTransformer->transform($inventory->dealerLocation) : null,
             'description' => $inventory->description,
             'entity_type_id' => $inventory->entity_type_id ,
             'fp_balance' => $inventory->fp_balance,
             'fp_interest_paid' => $inventory->interest_paid,
             'fp_committed' => $inventory->fp_committed,
             'gvwr' => $inventory->gvwr,
             'height' => $inventory->height,
             'images' => $this->transformImages($inventory->images),
             'primary_image' => $inventory->images->count() > 0 ? $this->imageTransformer->transform($inventory->images->first()) : null,
             'is_archived' => $inventory->is_archived,
             'is_floorplan_bill' => $inventory->is_floorplan_bill,
             'length' => $inventory->length,
             'manufacturer' => $inventory->manufacturer,
             'model' => $inventory->model,
             'msrp' => $inventory->msrp,
             'non_serialized' => $inventory->non_serialized,
             'note' => $inventory->note,
             'price' => $inventory->price ?? 0,
             'sales_price' => $inventory->sales_price ?? 0,
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
             'color' => $inventory->color,
             'floorplan_payments' => $inventory->floorplanPayments,
             'url' => $inventory->getUrl(),
             'floorplan_vendor' => $inventory->floorplanVendor,
             'created_at' => $inventory->created_at,
             'updated_at' => $inventory->updated_at
         ];
    }

    public function includeWebsite($inventory)
    {
        return $this->item($inventory->user->website, new WebsiteTransformer);
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
