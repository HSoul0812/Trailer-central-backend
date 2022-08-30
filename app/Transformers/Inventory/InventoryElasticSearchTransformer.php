<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use App\Helpers\Types;
use App\Models\Inventory\Attribute;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryFeature;
use App\Models\Inventory\InventoryImage;

class InventoryElasticSearchTransformer
{
    public function transform(Inventory $inventory): array
    {
        $primaryImages = $inventory->orderedPrimaryImages();
        $secondaryImages = $inventory->orderedSecondaryImages();
        $defaultImage = $primaryImages->first();
        $geolocation = $inventory->geolocation();

        return [
            'id'                   => Types::ensureNumeric($inventory->inventory_id),
            'dealerId'             => $inventory->dealer_id,
            'dealerLocationId'     => $inventory->dealer_location_id,
            'createdAt'            => Types::ensureDateString($inventory->created_at),
            'updatedAt'            => Types::ensureDateString($inventory->updated_at_auto),
            'isActive'             => Types::ensureBoolean($inventory->active),
            'isSpecial'            => Types::ensureBoolean($inventory->is_special),
            'isFeatured'           => Types::ensureBoolean($inventory->is_featured),
            'isArchived'           => Types::ensureBoolean($inventory->is_archived),
            'archivedAt'           => Types::ensureDateString($inventory->archived_at),
            'updatedAtUser'        => Types::ensureDateString($inventory->updated_at),
            'stock'                => $inventory->stock,
            'title'                => $inventory->title,
            'year'                 => Types::ensureInt($inventory->year),
            'manufacturer'         => $inventory->manufacturer,
            'brand'                => $inventory->brand ?: null,
            'model'                => $inventory->model,
            'description'          => $inventory->description,
            'description_html'     => $inventory->description_html,
            'status'               => $inventory->status,
            'availability'         => $inventory->availability,
            'availabilityLabel'    => $inventory->status_label,
            'typeLabel'            => $inventory->type_label,
            'category'             => $inventory->category,
            'categoryLabel'        => $inventory->category_label,
            'vin'                  => $inventory->vin,
            'msrpMin'              => Types::ensureBoolean($inventory->msrp_min),
            'msrp'                 => Types::ensureBoolean($inventory->msrp),
            'useWebsitePrice'      => Types::ensureBoolean($inventory->use_website_price),
            'websitePrice'         => Types::ensureNumeric($inventory->final_website_price),
            'originalWebsitePrice' => Types::ensureNumeric($inventory->website_price),
            'dealerPrice'          => Types::ensureNumeric($inventory->dealer_price),
            'salesPrice'           => Types::ensureNumeric($inventory->sales_price),
            'basicPrice'           => Types::ensureNumeric($inventory->basic_price),
            'monthlyPrice'         => Types::ensureNumeric($inventory->monthly_payment),
            'monthlyRate'          => $inventory->getAttributeById(Attribute::MONTHLY_PRICE, 0),
            'existingPrice'        => $inventory->existing_price,
            'condition'            => $inventory->condition,
            'length'               => Types::ensureNumeric($inventory->length),
            'width'                => Types::ensureNumeric($inventory->width),
            'height'               => Types::ensureNumeric($inventory->height),
            'weight'               => Types::ensureNumeric($inventory->weight),
            'gvwr'                 => Types::ensureNumeric($inventory->gvwr),
            'axleCapacity'         => Types::ensureNumeric($inventory->axle_capacity),
            'payloadCapacity'      => Types::ensureNumeric($inventory->payload_capacity),
            'costOfUnit'           => Types::ensureNumeric($inventory->cost_of_unit),
            'costOfShipping'       => Types::ensureNumeric($inventory->cost_of_shipping),
            'costOfPrep'           => Types::ensureNumeric($inventory->cost_of_prep),
            'totalOfCost'          => Types::ensureNumeric($inventory->total_of_cost),
            'minimumSellingPrice'  => Types::ensureNumeric($inventory->minimum_selling_price),
            'notes'                => $inventory->notes,
            'showOnKsl'            => Types::ensureBoolean($inventory->show_on_ksl),
            'showOnRacingjunk'     => Types::ensureBoolean($inventory->show_on_racingjunk),
            'showOnWebsite'        => Types::ensureBoolean($inventory->show_on_website),
            'videoEmbedCode'       => $inventory->video_embed_code,
            'numAc'                => Types::ensureNumeric($inventory->getAttributeById(Attribute::AIR_CONDITIONERS)),
            'numAxles'             => Types::ensureNumeric($inventory->getAttributeById(Attribute::AXLES)),
            'numBatteries'         => Types::ensureNumeric($inventory->getAttributeById(Attribute::NUMBER_BATTERIES)),
            'numPassengers'        => Types::ensureNumeric($inventory->getAttributeById(Attribute::PASSENGERS)),
            'numSleeps'            => Types::ensureNumeric($inventory->getAttributeById(Attribute::SLEEPING_CAPACITY)),
            'numSlideouts'         => Types::ensureNumeric($inventory->getAttributeById(Attribute::SLIDEOUTS)),
            'numStalls'            => Types::ensureNumeric($inventory->getAttributeById(Attribute::STALLS)),
            'conversion'           => $inventory->getAttributeById(Attribute::CONVERSION),
            'customConversion'     => $inventory->getAttributeById(Attribute::CUSTOM_CONVERSION),

            // not sure why ES worker wanted to index `shortwallFt` field using zero when it is not setup
            // so lets remain it as it is
            'shortwallFt'          => Types::ensureNumeric($inventory->getAttributeById(Attribute::SHORTWALL_LENGTH, 0)),
            'color'                => $inventory->getAttributeById(Attribute::COLOR),
            'pullType'             => $inventory->getAttributeById(Attribute::PULL_TYPE),
            'noseType'             => $inventory->getAttributeById(Attribute::NOSE_TYPE),
            'roofType'             => $inventory->getAttributeById(Attribute::ROOF_TYPE),
            'loadType'             => $inventory->getAttributeById(Attribute::CONFIGURATION),
            'fuelType'             => $inventory->getAttributeById(Attribute::FUEL_TYPE),
            'frameMaterial'        => $inventory->getAttributeById(Attribute::CONSTRUCTION),
            'horsepower'           => $inventory->getAttributeById(Attribute::HORSE_POWER),
            'hasLq'                => Types::ensureBoolean($inventory->getAttributeById(Attribute::LIVING_QUARTERS)),
            'hasManger'            => Types::ensureBoolean($inventory->getAttributeById(Attribute::MANGER)),
            'hasMidtack'           => Types::ensureBoolean($inventory->getAttributeById(Attribute::MIDTACK)),
            'hasRamps'             => Types::ensureBoolean($inventory->getAttributeById(Attribute::RAMPS)),

            // we cannot use $inventory->mileage, its default value is an empty space which we dont want to index by,
            // probably when that Eloquent accessor is fixed, then we could use it
            'mileage'              => Types::ensureNumeric($inventory->getAttributeById(Attribute::MILEAGE)),
            'mileageMiles'         => Types::ensureNumeric($inventory->mileage_miles),
            'mileageKilometres'    => Types::ensureNumeric($inventory->mileage_kilometres),
            'isRental'             => Types::ensureBoolean($inventory->getAttributeById(Attribute::IS_RENTAL)),
            'weeklyPrice'          => Types::ensureNumeric($inventory->getAttributeById(Attribute::WEEKLY_PRICE)),
            'dailyPrice'           => Types::ensureNumeric($inventory->getAttributeById(Attribute::DAILY_PRICE)),
            'floorplan'            => $inventory->getAttributeById(Attribute::FLOORPLAN), // maybe this field is deprecated

            // the following fields are not being considered by the ES worker to be pulled,
            // however they are defined in the index map, so we will index them again
            'cabType'              => $inventory->getAttributeById(Attribute::CAB_TYPE),
            'engineSize'           => Types::ensureNumeric($inventory->getAttributeById(Attribute::ENGINE_SIZE)),
            'transmission'         => $inventory->getAttributeById(Attribute::TRANSMISSION),
            'driveTrail'           => $inventory->getAttributeById(Attribute::DRIVE_TRAIL),
            'propulsion'           => $inventory->getAttributeById(Attribute::PROPULSION),
            'draft'                => Types::ensureNumeric($inventory->getAttributeById(Attribute::DRAFT)),
            'transom'              => Types::ensureNumeric($inventory->getAttributeById(Attribute::TRANSOM)),
            'deadRise'             => $inventory->getAttributeById(Attribute::DEAD_RISE),
            'totalWeightCapacity'  => $inventory->getAttributeById(Attribute::TOTAL_WEIGHT_CAPACITY),
            'wetWeight'            => $inventory->getAttributeById(Attribute::WET_WEIGHT),
            'seatingCapacity'      => $inventory->getAttributeById(Attribute::SEATING_CAPACITY),
            'hullType'             => $inventory->getAttributeById(Attribute::HULL_TYPE),
            'engineHours'          => Types::ensureNumeric($inventory->getAttributeById(Attribute::ENGINE_HOURS)),
            'dryWeight'            => Types::ensureNumeric($inventory->getAttributeById(Attribute::DRY_WEIGHT)),
            'interiorColor'        => $inventory->getAttributeById(Attribute::INTERIOR_COLOR),
            'hitchWeight'          => $inventory->getAttributeById(Attribute::HITCH_WEIGHT),
            'cargoWeight'          => $inventory->getAttributeById(Attribute::CARGO_WEIGHT),
            'freshWaterCapacity'   => $inventory->getAttributeById(Attribute::FRESH_WATER_CAPACITY),
            'grayWaterCapacity'    => $inventory->getAttributeById(Attribute::GRAY_WATER_CAPACITY),
            'blackWaterCapacity'   => $inventory->getAttributeById(Attribute::BLACK_WATER_CAPACITY),
            'furnaceBtu'           => $inventory->getAttributeById(Attribute::FURNACE_BTU),
            'acBtu'                => $inventory->getAttributeById(Attribute::AC_BTU),
            'electricalService'    => $inventory->getAttributeById(Attribute::ELECTRICAL_SERVICE),
            'availableBeds'        => $inventory->getAttributeById(Attribute::AVAILABLE_BEDS),
            'numberAwnings'        => Types::ensureInt($inventory->getAttributeById(Attribute::NUMBER_AWNINGS)),
            'awningSize'           => $inventory->getAttributeById(Attribute::NUMBER_AWNINGS),
            'axleWeight'           => $inventory->getAttributeById(Attribute::AXLE_WEIGHT),
            'engine'               => $inventory->getAttributeById(Attribute::ENGINE),
            'fuelCapacity'         => $inventory->getAttributeById(Attribute::FUEL_CAPACITY),
            'sideWallHeight'       => $inventory->getAttributeById(Attribute::SIDE_WALL_HEIGHT),
            'externalLink'         => $inventory->getAttributeById(Attribute::SIDE_WALL_HEIGHT),
            'subtitle'             => $inventory->getAttributeById(Attribute::SUBTITLE),
            'overallLength'        => Types::ensureNumeric($inventory->getAttributeById(Attribute::OVERALL_LENGTH)),
            'minWidth'             => Types::ensureNumeric($inventory->getAttributeById(Attribute::MIN_WIDTH)),
            'minHeight'            => Types::ensureNumeric($inventory->getAttributeById(Attribute::MIN_HEIGHT)),
            'monthlyPrice2'        => Types::ensureNumeric($inventory->getAttributeById(Attribute::MONTHLY_PRICE)),

            'dealer.name'          => $inventory->user->name,
            'dealer.email'         => $inventory->user->email,
            'location.name'        => $inventory->dealerLocation->name,
            'location.contact'     => $inventory->dealerLocation->contact,
            'location.website'     => $inventory->dealerLocation->website,
            'location.phone'       => $inventory->dealerLocation->phone,
            'location.address'     => $inventory->dealerLocation->address,
            'location.city'        => $inventory->dealerLocation->city,
            'location.region'      => $inventory->dealerLocation->region,
            'location.postalCode'  => $inventory->dealerLocation->postalcode,
            'location.country'     => $inventory->dealerLocation->country,
            'location.geo'         => [
                'lat' => Types::ensureNumeric($geolocation->latitude), // coordinate name must remain
                'long' => Types::ensureNumeric($geolocation->longitude), // coordinate name must remain
            ],
            'keywords'              => [],// currently this is sending nothing

            'featureList.floorPlan' => $inventory->getFeatureById(InventoryFeature::FLOORPLAN)->values()->toArray(),
            'featureList.stallTack' => $inventory->getFeatureById(InventoryFeature::STALL_TACK)->values()->toArray(),
            'featureList.lq'        => $inventory->getFeatureById(InventoryFeature::LQ)->values()->toArray(),
            'featureList.doorsWindowsRamps'=> $inventory->getFeatureById(InventoryFeature::DOORS_WINDOWS_RAMPS)->values()->toArray(),

            'image'                => $defaultImage ? $defaultImage->image->filename : null,
            'images'               => $primaryImages->map($this->imagesMapper())->values()->toArray(),
            'imagesSecondary'      => $secondaryImages->map($this->imagesMapper())->values()->toArray(),
            'numberOfImages'       => $primaryImages->count() + $secondaryImages->count(),
            'widthInches'          => Types::ensureNumeric($inventory->width_inches),
            'heightInches'         => Types::ensureNumeric($inventory->height_inches),
            'lengthInches'         => Types::ensureNumeric($inventory->length_inches),
            'widthDisplayMode'     => $inventory->width_display_mode,
            'heightDisplayMode'    => $inventory->height_display_mode,
            'lengthDisplayMode'    => $inventory->length_display_mode,
            'tilt'                 => $inventory->getAttributeById(Attribute::TILT),
            'entity_type_id'       => $inventory->entity_type_id
        ];
    }

    private function imagesMapper(): callable
    {
        return static function (InventoryImage $image) {
            return $image->image->filename;
        };
    }
}
