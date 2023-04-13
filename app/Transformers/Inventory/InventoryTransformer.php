<?php

namespace App\Transformers\Inventory;

use App\Helpers\ConvertHelper;
use App\Helpers\Inventory\InventoryHelper;
use App\Models\Inventory\File;
use App\Models\Inventory\InventoryImage;
use App\Repositories\Website\PaymentCalculator\SettingsRepositoryInterface;
use App\Transformers\Dms\ServiceOrderTransformer;
use App\Transformers\Marketing\Facebook\ListingTransformer;
use Illuminate\Database\Eloquent\Collection;
use League\Fractal\Resource\Item;
use Carbon\Carbon;
use League\Fractal\Resource\Primitive;
use League\Fractal\TransformerAbstract;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryFeature;
use App\Models\Inventory\Attribute;
use App\Transformers\User\UserTransformer;
use App\Transformers\User\DealerLocationTransformer;
use App\Transformers\Website\WebsiteTransformer;
use League\Fractal\Resource\Collection as FractalCollection;

class InventoryTransformer extends TransformerAbstract
{
    protected const CRM_NEW_QUOTE_URL = '/bill-of-sale/new';

    protected $availableIncludes = [
        'website',
        'repairOrders',
        'attributes',
        'features',
        'clapps',
        'activeListings',
        'paymentCalculator',
        'attributeValues',
        'inventoryFeatures'
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

    /**
     * @var ConvertHelper
     */
    private $convertHelper;

    public function __construct()
    {
        $this->userTransformer = new UserTransformer;
        $this->dealerLocationTransformer = new DealerLocationTransformer;
        $this->inventoryImageTransformer = new InventoryImageTransformer;
        $this->fileTransformer = new FileTransformer;
        $this->attributeValueTransformer = new AttributeValueTransformer;
        $this->featureTransformer = new FeatureTransformer;
        $this->clappTransformer = new ClappTransformer;

        $this->convertHelper = new ConvertHelper();
    }

    /**
     * @param Inventory $inventory
     * @return array
     */
    public function transform(Inventory $inventory): array
    {
        $lengthDimension = $inventory->length_inches ?: $inventory->length;

        if ($lengthDimension > 0) {
            list($lengthSecond, $lengthInchesSecond) = $this->convertHelper->feetToFeetInches($lengthDimension);
        }

        $widthDimension = $inventory->width_inches ?: $inventory->width;

        if ($widthDimension > 0) {
            list($widthSecond, $widthInchesSecond) = $this->convertHelper->feetToFeetInches($widthDimension);
        }

        $heightDimension = $inventory->height_inches ?: $inventory->height;

        if ($heightDimension > 0) {
            list($heightSecond, $heightInchesSecond) = $this->convertHelper->feetToFeetInches($heightDimension);
        }

        $age = now()->diffInDays(Carbon::parse($inventory->created_at));

        if ($dateSoldOrArchived = $inventory->archived_at ?? $inventory->sold_at) {
            $age = Carbon::parse($dateSoldOrArchived)->diffInDays(Carbon::parse($inventory->created_at));
        }

        return [
             'id' => $inventory->inventory_id,
             'identifier' => $inventory->identifier,
             'active' => $inventory->active,
             'archived_at' => $inventory->archived_at,
             'payload_capacity' => $inventory->payload_capacity,
             'availability' => $inventory->availability,
             'bill_id' => $inventory->bill_id,
             'brand' => $inventory->brand,
             'category' => $inventory->category,
             'category_label' => $inventory->category_label,
             'condition' => $inventory->condition,
             'dealer' => $this->userTransformer->transform($inventory->user),
             'dealer_location_id' => $inventory->dealer_location_id,
             'dealer_location' => $inventory->dealerLocation ? $this->dealerLocationTransformer->transform($inventory->dealerLocation) : null,
             'description' => $this->fixWrongChars($inventory->description),
             'description_html' => $inventory->description_html,
             'entity_type_id' => $inventory->entity_type_id,
             'fp_balance' => $inventory->fp_balance,
             'fp_interest_paid' => $inventory->interest_paid,
             'fp_committed' => $inventory->fp_committed,
             'fp_paid' => $inventory->fp_paid,
             'gvwr' => $inventory->gvwr,
             'axle_capacity' => $inventory->axle_capacity,
             'height_display_mode' => $inventory->height_display_mode,
             'height' => $inventory->height,
             'height_inches' => $inventory->height_inches,
             'height_second' => $heightSecond ?? 0,
             'height_inches_second' => $heightInchesSecond ?? 0,
             'images' => $this->transformImages($inventory->inventoryImages),
             'files' => $this->transformFiles($inventory->files),
             'primary_image' => $inventory->images->count() > 0 ?
                    $this->inventoryImageTransformer->transform($inventory->inventoryImages->sortBy($this->imageSorter())->first()) :
                    null,
             'is_archived' => $inventory->is_archived,
             'is_floorplan_bill' => $inventory->is_floorplan_bill,
             'floor_plans' => $inventory->getFeatureById(InventoryFeature::FLOORPLAN)->values()->toArray(),
             'length' => $inventory->length,
             'length_inches' => $inventory->length_inches,
             'length_second' => $lengthSecond ?? null,
             'length_inches_second' => $lengthInchesSecond ?? null,
             'length_display_mode' => $inventory->length_display_mode,
             'manufacturer' => $inventory->manufacturer,
             'model' => $inventory->model,
             'msrp' => $inventory->msrp,
             'non_serialized' => $inventory->non_serialized,
             'notes' => $inventory->notes,
             'price' => $inventory->price ?? 0,
             'sales_price' => (float) ($inventory->sales_price ?? 0),
             'website_price' => $inventory->website_price ?? 0,
             'send_to_quickbooks' => $inventory->send_to_quickbooks,
             'status' => $inventory->status_label,
             'status_id' => $inventory->status,
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
             'width_inches' => $inventory->width_inches,
             'width_second' => $widthSecond ?? null,
             'width_inches_second' => $widthInchesSecond ?? null,
             'width_display_mode' => $inventory->width_display_mode,
             'year' => $inventory->year,
             'chassis_year' => $inventory->chassis_year,
             'color' => $inventory->color,
             'floorplan_payments' => $inventory->floorplanPayments,
             'url' => $inventory->getUrl(),
             'floorplan_vendor' => $inventory->floorplanVendor,
             'created_at' => $inventory->created_at,
             'updated_at' => $inventory->updated_at,
             'updated_at_auto' => $inventory->updated_at_auto,
             'times_viewed' => $inventory->times_viewed,
             'sold_at' => $inventory->sold_at,
             'is_featured' => $inventory->is_featured,
             'is_special' => $inventory->is_special,
             'is_rental' => (bool)$inventory->getAttributeById(Attribute::IS_RENTAL),
             'chosen_overlay' => $inventory->chosen_overlay,
             'hidden_price' => $inventory->hidden_price,
             'monthly_payment' => $inventory->monthly_payment,
             'show_on_website' => $inventory->show_on_website,
             'tt_payment_expiration_date' => $inventory->tt_payment_expiration_date,
             'overlay_enabled' => $inventory->overlay_enabled,
             'cost_of_ros' => $inventory->cost_of_ros,
             'quote_url' => optional($inventory->user)->getCrmLoginUrl(
                 $this->getNewQuoteRoute($inventory->identifier),
                 true
             ),
             'fuel_type' => $inventory->getAttributeById(Attribute::FUEL_TYPE),
             'mileage' => $inventory->getAttributeById(Attribute::MILEAGE),
             'mileage_miles' => $inventory->mileage_miles,
             'mileage_kilometres' => $inventory->mileage_kilometers,
             'sleeping_capacity' => $inventory->getAttributeById(Attribute::SLEEPING_CAPACITY),
             'age' => $age,
             'use_website_price' => $inventory->use_website_price,
             'minimum_selling_price' => $inventory->minimum_selling_price,
             'pac_type' => $inventory->pac_type,
             'pac_amount' => $inventory->pac_amount,
             'show_on_ksl' => $inventory->show_on_ksl,
             'show_on_racingjunk' => $inventory->show_on_racingjunk,
             'show_on_rvtrader' => $inventory->show_on_rvtrader,
             'changed_fields_in_dashboard' => $inventory->changed_fields_in_dashboard,
             'show_on_auction123' => $inventory->show_on_auction123,
             'show_on_rvt' => $inventory->show_on_rvt
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
    public function includeAttributeValues(Inventory $inventory): FractalCollection
    {
        return $this->includeAttributes($inventory);
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
    public function includeInventoryFeatures(Inventory $inventory): FractalCollection
    {
        return $this->includeFeatures($inventory);
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
     * @param Inventory $inventory
     * @return array|FractalCollection
     */
    public function includeActiveListings(Inventory $inventory)
    {
        if (empty($inventory->activeListings)) {
            return [];
        }

        return $this->collection($inventory->activeListings, new ListingTransformer);
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

    public function includePaymentCalculator(Inventory $inventory): Primitive
    {
        return $this->primitive($this->settingsRepository()->getCalculatedSettingsByInventory($inventory));
    }

    /**
     * @param Collection $images
     * @return array
     */
    private function transformImages(Collection $images): array
    {
        return $images->sortBy($this->imageSorter())->values()->map(function (InventoryImage $image) {
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

    /**
     * @param string $inventoryIdentifier
     * @return string
     */
    private function getNewQuoteRoute(string $inventoryIdentifier): string
    {
        return self::CRM_NEW_QUOTE_URL . '?inventory_id=' . $inventoryIdentifier;
    }

    /**
     * Due we have some malformed text values like description, this is a mitigation fix while we found a way to fix it
     * in the importers processes
     *
     * @param string|null $rawInput
     * @return string
     */
    private function fixWrongChars(?string $rawInput): string
    {
        return str_replace([
            '\n',
            '\\' . PHP_EOL,
            '\\' . PHP_EOL . 'n'
        ], [
            PHP_EOL,
            PHP_EOL . PHP_EOL,
            PHP_EOL . PHP_EOL . PHP_EOL
        ], $rawInput);
    }

    /**
     * Sorts the inventory images ensuring that the image which is `is_default=1` always will be the first image,
     * also, if the image has NULL as position, then, that image will be sorted at last position.
     *
     * That sorting strategy was extracted from the ES worker.
     *
     * @return callable
     */
    private function imageSorter(): callable
    {
        return InventoryHelper::singleton()->imageSorter();
    }

    protected function settingsRepository(): SettingsRepositoryInterface
    {
        return app(SettingsRepositoryInterface::class);
    }
}
