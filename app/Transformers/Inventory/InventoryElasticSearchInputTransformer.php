<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use App\Helpers\TypesHelper;
use App\Indexers\Transformer;
use App\Models\Inventory\Attribute;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryFeature;
use App\Models\Inventory\InventoryImage;
use App\Repositories\Website\PaymentCalculator\SettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class InventoryElasticSearchInputTransformer implements Transformer
{
    /**
     * @param Inventory $model
     * @return array
     */
    public function transform($model): array
    {
        $primaryImages = $this->transformPrimaryImages($model->inventoryImages);
        $originalImages = $this->transformOriginalImages($model);
        $secondaryImages = $this->transformSecondaryImages($model->inventoryImages);
        $defaultImage = $primaryImages[0] ?? null;
        $geolocation = $model->geolocationPoint();
        $paymentCalculatorSettings = $this->settingsRepository()->getCalculatedSettingsByInventory($model);

        return [
            'id'                   => TypesHelper::ensureNumeric($model->inventory_id),
            'dealerId'             => $model->dealer_id,
            'dealerLocationId'     => $model->dealer_location_id,
            'createdAt'            => TypesHelper::ensureDateString($model->created_at),
            'updatedAt'            => TypesHelper::ensureDateString($model->updated_at_auto),
            'isActive'             => TypesHelper::ensureBoolean($model->active),
            'isSpecial'            => TypesHelper::ensureBoolean($model->is_special),
            'isFeatured'           => TypesHelper::ensureBoolean($model->is_featured),
            'isArchived'           => TypesHelper::ensureBoolean($model->is_archived),
            'archivedAt'           => TypesHelper::ensureDateString($model->archived_at),
            'updatedAtUser'        => TypesHelper::ensureDateString($model->updated_at),
            'isClassified'        => (bool)$model->user->clsf_active,
            'stock'                => $model->stock,
            'title'                => $model->title,
            'year'                 => TypesHelper::ensureInt($model->year),
            'manufacturer'         => $model->manufacturer,
            'brand'                => $model->brand ?: null,
            'model'                => $model->model,
            'description'          => $model->description,
            'description_html'     => $model->description_html,
            'status'               => $model->status ?? Inventory::STATUS_AVAILABLE,
            'availability'         => $model->availability ?? strtolower(Inventory::STATUS_AVAILABLE_LABEL),
            'availabilityLabel'    => $model->status_label ?? Inventory::STATUS_AVAILABLE_LABEL,
            'typeLabel'            => $model->type_label,
            'category'             => $model->category,
            'categoryLabel'        => $model->category_label,
            'vin'                  => $model->vin,
            'msrpMin'              => TypesHelper::ensureNumeric($model->msrp_min),
            'msrp'                 => TypesHelper::ensureNumeric($model->msrp),
            'useWebsitePrice'      => TypesHelper::ensureBoolean($model->use_website_price),
            'websitePrice'         => TypesHelper::ensureNumeric($model->final_website_price),
            'originalWebsitePrice' => TypesHelper::ensureNumeric($model->website_price),
            'dealerPrice'          => TypesHelper::ensureNumeric($model->dealer_price),
            'salesPrice'           => TypesHelper::ensureNumeric($model->sales_price),
            'basicPrice'           => TypesHelper::ensureNumeric($model->basic_price),
            'monthlyPrice'         => TypesHelper::ensureNumeric($model->monthly_payment),
            'monthlyRate'          => $model->getAttributeById(Attribute::MONTHLY_PRICE, 0),
            'existingPrice'        => $model->existing_price,
            'condition'            => $model->condition,
            'length'               => TypesHelper::ensureNumeric($model->length),
            'width'                => TypesHelper::ensureNumeric($model->width),
            'height'               => TypesHelper::ensureNumeric($model->height),
            'weight'               => TypesHelper::ensureNumeric($model->weight),
            'gvwr'                 => TypesHelper::ensureNumeric($model->gvwr),
            'axleCapacity'         => TypesHelper::ensureNumeric($model->axle_capacity),
            'payloadCapacity'      => TypesHelper::ensureNumeric($model->payload_capacity),
            'costOfUnit'           => TypesHelper::ensureNumeric($model->cost_of_unit),
            'costOfShipping'       => TypesHelper::ensureNumeric($model->cost_of_shipping),
            'costOfPrep'           => TypesHelper::ensureNumeric($model->cost_of_prep),
            'totalOfCost'          => TypesHelper::ensureNumeric($model->total_of_cost),
            'minimumSellingPrice'  => TypesHelper::ensureNumeric($model->minimum_selling_price),
            'notes'                => $model->notes,
            'showOnKsl'            => TypesHelper::ensureBoolean($model->show_on_ksl),
            'showOnRacingjunk'     => TypesHelper::ensureBoolean($model->show_on_racingjunk),
            'showOnWebsite'        => TypesHelper::ensureBoolean($model->show_on_website),
            'videoEmbedCode'       => $model->video_embed_code,
            'numAc'                => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::AIR_CONDITIONERS)),
            'numAxles'             => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::AXLES)),
            'numBatteries'         => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::NUMBER_BATTERIES)),
            'numPassengers'        => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::PASSENGERS)),
            'numSleeps'            => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::SLEEPING_CAPACITY)),
            'numSlideouts'         => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::SLIDEOUTS)),
            'numStalls'            => $model->getAttributeById(Attribute::STALLS),
            'conversion'           => $model->getAttributeById(Attribute::CONVERSION),
            'customConversion'     => $model->getAttributeById(Attribute::CUSTOM_CONVERSION),

            // not sure why ES worker wanted to index `shortwallFt` field using zero when it is not setup
            // so lets remain it as it is
            'shortwallFt'          => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::SHORTWALL_LENGTH, 0)),
            'color'                => $model->getAttributeById(Attribute::COLOR),
            'pullType'             => $model->getAttributeById(Attribute::PULL_TYPE),
            'noseType'             => $model->getAttributeById(Attribute::NOSE_TYPE),
            'roofType'             => $model->getAttributeById(Attribute::ROOF_TYPE),
            'loadType'             => $model->getAttributeById(Attribute::CONFIGURATION),
            'fuelType'             => $model->getAttributeById(Attribute::FUEL_TYPE),
            'frameMaterial'        => $model->getAttributeById(Attribute::CONSTRUCTION),
            'horsepower'           => $model->getAttributeById(Attribute::HORSE_POWER),
            'hasLq'                => TypesHelper::ensureBoolean($model->getAttributeById(Attribute::LIVING_QUARTERS)),
            'hasManger'            => TypesHelper::ensureBoolean($model->getAttributeById(Attribute::MANGER)),
            'hasMidtack'           => TypesHelper::ensureBoolean($model->getAttributeById(Attribute::MIDTACK)),
            'hasRamps'             => TypesHelper::ensureBoolean($model->getAttributeById(Attribute::RAMPS)),

            // we cannot use $model->mileage, its default value is an empty space which we dont want to index by,
            // probably when that Eloquent accessor is fixed, then we could use it
            'mileage'              => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::MILEAGE)),
            'mileageMiles'         => TypesHelper::ensureNumeric($model->mileage_miles),
            'mileageKilometers'    => TypesHelper::ensureNumeric($model->mileage_kilometers),
            // Handle isRental somewhat specially.
            // If it isn't explicity set to yes, then explicitly set it to no.
            'isRental'             => (bool)TypesHelper::ensureBoolean($model->getAttributeById(Attribute::IS_RENTAL)),
            'weeklyPrice'          => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::WEEKLY_PRICE)),
            'dailyPrice'           => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::DAILY_PRICE)),
            'floorplan'            => $model->getAttributeById(Attribute::FLOORPLAN), // maybe this field is deprecated

            // the following fields are not being considered by the ES worker to be pulled,
            // however they are defined in the index map, so we will index them again
            'cabType'              => $model->getAttributeById(Attribute::CAB_TYPE),
            'engineSize'           => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::ENGINE_SIZE)),
            'transmission'         => $model->getAttributeById(Attribute::TRANSMISSION),
            'driveTrail'           => $model->getAttributeById(Attribute::DRIVE_TRAIL),
            'propulsion'           => $model->getAttributeById(Attribute::PROPULSION),
            'draft'                => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::DRAFT)),
            'transom'              => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::TRANSOM)),
            'deadRise'             => $model->getAttributeById(Attribute::DEAD_RISE),
            'totalWeightCapacity'  => $model->getAttributeById(Attribute::TOTAL_WEIGHT_CAPACITY),
            'wetWeight'            => $model->getAttributeById(Attribute::WET_WEIGHT),
            'seatingCapacity'      => $model->getAttributeById(Attribute::SEATING_CAPACITY),
            'hullType'             => $model->getAttributeById(Attribute::HULL_TYPE),
            'engineHours'          => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::ENGINE_HOURS)),
            'dryWeight'            => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::DRY_WEIGHT)),
            'interiorColor'        => $model->getAttributeById(Attribute::INTERIOR_COLOR),
            'hitchWeight'          => $model->getAttributeById(Attribute::HITCH_WEIGHT),
            'cargoWeight'          => $model->getAttributeById(Attribute::CARGO_WEIGHT),
            'freshWaterCapacity'   => $model->getAttributeById(Attribute::FRESH_WATER_CAPACITY),
            'grayWaterCapacity'    => $model->getAttributeById(Attribute::GRAY_WATER_CAPACITY),
            'blackWaterCapacity'   => $model->getAttributeById(Attribute::BLACK_WATER_CAPACITY),
            'furnaceBtu'           => $model->getAttributeById(Attribute::FURNACE_BTU),
            'acBtu'                => $model->getAttributeById(Attribute::AC_BTU),
            'electricalService'    => $model->getAttributeById(Attribute::ELECTRICAL_SERVICE),
            'availableBeds'        => $model->getAttributeById(Attribute::AVAILABLE_BEDS),
            'numberAwnings'        => TypesHelper::ensureInt($model->getAttributeById(Attribute::NUMBER_AWNINGS)),
            'awningSize'           => $model->getAttributeById(Attribute::NUMBER_AWNINGS),
            'axleWeight'           => $model->getAttributeById(Attribute::AXLE_WEIGHT),
            'engine'               => $model->getAttributeById(Attribute::ENGINE),
            'fuelCapacity'         => $model->getAttributeById(Attribute::FUEL_CAPACITY),
            'sideWallHeight'       => $model->getAttributeById(Attribute::SIDE_WALL_HEIGHT),
            'externalLink'         => $model->getAttributeById(Attribute::SIDE_WALL_HEIGHT),
            'subtitle'             => $model->getAttributeById(Attribute::SUBTITLE),
            'overallLength'        => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::OVERALL_LENGTH)),
            'minWidth'             => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::MIN_WIDTH)),
            'minHeight'            => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::MIN_HEIGHT)),
            'monthlyPrice2'        => TypesHelper::ensureNumeric($model->getAttributeById(Attribute::MONTHLY_PRICE)),

            'dealer.name'          => $model->user->name,
            'dealer.email'         => $model->user->email,
            'location.name'        => $model->dealerLocation->name,
            'location.contact'     => $model->dealerLocation->contact,
            'location.website'     => $model->dealerLocation->website,
            'location.phone'       => $model->dealerLocation->phone,
            'location.address'     => $model->dealerLocation->address,
            'location.city'        => $model->dealerLocation->city,
            'location.region'      => $model->dealerLocation->region,
            'location.postalCode'  => $model->dealerLocation->postalcode,
            'location.country'     => $model->dealerLocation->country,
            'location.geo'         => [
                'lat' => TypesHelper::ensureNumeric($geolocation->latitude), // coordinate name must remain
                'lon' => TypesHelper::ensureNumeric($geolocation->longitude), // coordinate name must remain
            ],
            'keywords'              => [],// currently this is sending nothing

            'featureList.floorPlan' => $model->getFeatureById(InventoryFeature::FLOORPLAN)->values()->toArray(),
            'featureList.stallTack' => $model->getFeatureById(InventoryFeature::STALL_TACK)->values()->toArray(),
            'featureList.lq'        => $model->getFeatureById(InventoryFeature::LQ)->values()->toArray(),
            'featureList.doorsWindowsRamps'=> $model->getFeatureById(InventoryFeature::DOORS_WINDOWS_RAMPS)->values()->toArray(),

            'image'                => $defaultImage,
            'images'               => $primaryImages,
            'originalImages'       => $originalImages,
            'imagesSecondary'      => $secondaryImages,
            'numberOfImages'       => count($primaryImages) + count($secondaryImages),
            'widthInches'          => TypesHelper::ensureNumeric($model->width_inches),
            'heightInches'         => TypesHelper::ensureNumeric($model->height_inches),
            'lengthInches'         => TypesHelper::ensureNumeric($model->length_inches),
            'widthDisplayMode'     => $model->width_display_mode,
            'heightDisplayMode'    => $model->height_display_mode,
            'lengthDisplayMode'    => $model->length_display_mode,
            'tilt'                 => $model->getAttributeById(Attribute::TILT),
            'entity_type_id'       => $model->entity_type_id,
            'paymentCalculator' => $paymentCalculatorSettings
        ];
    }

    protected function settingsRepository(): SettingsRepositoryInterface
    {
        return app(SettingsRepositoryInterface::class);
    }

    private function imageSorter(): callable
    {
        return static function (InventoryImage $image): int {
            // when the position is null, it will sorted a last position
            $position = $image->position ?? InventoryImage::LAST_IMAGE_POSITION;

            return $image->isDefault() ? InventoryImage::FIRST_IMAGE_POSITION : $position;
        };
    }

    /**
     * @param Collection $images
     * @return array
     */
    private function transformPrimaryImages(Collection $images): array
    {
        return $images->sortBy($this->imageSorter())->values()->filter(function (InventoryImage $image) {
            return !$image->isSecondary();
        })->map(function (InventoryImage $image) { return $image->image->filename; })->values()->toArray();
    }

    /**
     * @param Model $inventory
     * @return array
     */
    private function transformOriginalImages(Inventory $inventory): array
    {
        return $inventory->inventoryImages->sortBy($this->imageSorter())->values()->filter(function (InventoryImage $image) {
            return !$image->isSecondary();
        })->map(function (InventoryImage $image) use ($inventory) {
            if ($inventory->overlay_enabled == Inventory::OVERLAY_ENABLED_ALL) {
                return $image->image->filename_noverlay ? $image->image->filename_noverlay  : $image->image->filename;
            } elseif($inventory->overlay_enabled == Inventory::OVERLAY_ENABLED_PRIMARY && ($image->position == 1 || $image->is_default == 1))  {
                return $image->image->filename_noverlay ? $image->image->filename_noverlay  : $image->image->filename;
            }

            return $image->image->filename;

        })->values()->toArray();
    }

    /**
     * @param Collection $images
     * @return array
     */
    private function transformSecondaryImages(Collection $images): array
    {
        return $images->sortBy($this->imageSorter())->values()->filter(function (InventoryImage $image) {
            return $image->isSecondary();
        })->map(function (InventoryImage $image) { return $image->image->filename; })->values()->toArray();
    }
}
