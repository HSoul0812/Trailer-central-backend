<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\File;
use App\Models\Inventory\InventoryImage;
use App\Transformers\Dms\ServiceOrderTransformer;
use Illuminate\Database\Eloquent\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use App\Models\Inventory\Inventory;
use App\Transformers\User\UserTransformer;
use App\Transformers\User\DealerLocationTransformer;
use App\Transformers\Website\WebsiteTransformer;
use League\Fractal\Resource\Collection as FractalCollection;

/**
 * Class InventoryTransformer
 * @package App\Transformers\Inventory
 */
class InventoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'website',
        'repairOrders',
        'attributes',
        'features',
        'clapps',
    ];

    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    /**
     * @var DealerLocationTransformer
     */
    protected $dealerLocationTransformer;

    /**
     * @var InventoryImageTransformer
     */
    protected $inventoryImageTransformer;

    /**
     * @var AttributeValueTransformer
     */
    private $attributeValueTransformer;

    /**
     * @var FeatureTransformer
     */
    private $featureTransformer;

    /**
     * @var FileTransformer
     */
    private $fileTransformer;

    /**
     * @var ClappTransformer
     */
    private $clappTransformer;

    public function __construct() {
        $this->userTransformer = new UserTransformer;
        $this->dealerLocationTransformer = new DealerLocationTransformer;
        $this->inventoryImageTransformer = new InventoryImageTransformer;
        $this->fileTransformer = new FileTransformer;
        $this->attributeValueTransformer = new AttributeValueTransformer;
        $this->featureTransformer = new FeatureTransformer;
        $this->clappTransformer = new ClappTransformer;
    }

    /**
     * @param Inventory $inventory
     * @return array
     */
    public function transform(Inventory $inventory): array
    {
        return [
             'id' => $inventory->inventory_id,
             'identifier' => $inventory->identifier,
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
             'images' => $this->transformImages($inventory->inventoryImages),
             'files' => $this->transformFiles($inventory->files),
             'primary_image' => $inventory->images->count() > 0 ? $this->inventoryImageTransformer->transform($inventory->inventoryImages->first()) : null,
             'is_archived' => $inventory->is_archived,
             'is_floorplan_bill' => $inventory->is_floorplan_bill,
             'length' => $inventory->length,
             'manufacturer' => $inventory->manufacturer,
             'model' => $inventory->model,
             'msrp' => $inventory->msrp,
             'non_serialized' => $inventory->non_serialized,
             'notes' => $inventory->notes,
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
             'updated_at' => $inventory->updated_at,
             'times_viewed' => $inventory->times_viewed,
             'quote_url' => config('app.new_design_crm_url') . $inventory->user->getCrmLoginUrl('bill-of-sale/new?inventory_id=' . $inventory->identifier)
         ];
    }

    /**
     * @param Inventory $inventory
     * @return FractalCollection
     */
    public function includeAttributes(Inventory $inventory): FractalCollection
    {
        return $this->collection($inventory->attributeValues, $this->attributeValueTransformer);
    }

    /**
     * @param Inventory $inventory
     * @return FractalCollection
     */
    public function includeFeatures(Inventory $inventory): FractalCollection
    {
        return $this->collection($inventory->inventoryFeatures, $this->featureTransformer);
    }

    /**
     * @param Inventory $inventory
     * @return FractalCollection
     */
    public function includeClapps(Inventory $inventory): FractalCollection
    {
        return $this->collection($inventory->clapps, $this->clappTransformer);
    }

    /**
     * @param Inventory $inventory
     * @return Item
     */
    public function includeWebsite(Inventory $inventory): Item
    {
        return $this->item($inventory->user->website, new WebsiteTransformer);
    }

    /**
     * @param $inventory
     * @return array|FractalCollection
     */
    public function includeRepairOrders($inventory)
    {
        if (empty($inventory->repairOrders)) {
            return [];
        }

        return $this->collection($inventory->repairOrders, new ServiceOrderTransformer());
    }

    /**
     * @param Collection $images
     * @return array
     */
    private function transformImages(Collection $images): array
    {
        return $images->map(function (InventoryImage $image) {
            return $this->inventoryImageTransformer->transform($image);
        })->toArray();
    }

    /**
     * @param Collection $files
     * @return array
     */
    private function transformFiles(Collection $files): array
    {
        return $files->map(function (File $file) {
            return $this->fileTransformer->transform($file);
        })->toArray();
    }
}
