<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

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
            'id'                   => $inventory->inventory_id,
            'dealerId'             => $inventory->dealer_id,
            'dealerLocationId'     => $inventory->dealer_location_id,
            'createdAt'            => $inventory->created_at->toDateTimeString(),
            'updatedAt'            => $inventory->updated_at_auto ? $inventory->created_at->toDateTimeString() : null,
            'isActive'             => $inventory->active,
            'isSpecial'            => $inventory->is_special,
            'isFeatured'           => $inventory->is_featured,
            'isArchived'           => $inventory->is_archived,
            'archivedAt'           => $inventory->archived_at ? $inventory->archived_at->toDateTimeString() : null,
            'updatedAtUser'        => $inventory->updated_at ? $inventory->updated_at->toDateTimeString() : null,
            'stock'                => $inventory->stock,
            'title'                => $inventory->title,
            'year'                 => $inventory->year,
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
            'msrpMin'              => $inventory->msrp_min,
            'msrp'                 => $inventory->msrp,
            'useWebsitePrice'      => $inventory->use_website_price,
            'websitePrice'         => $inventory->final_website_price,
            'originalWebsitePrice' => $inventory->website_price,
            'dealerPrice'          => $inventory->dealer_price,
            'salesPrice'           => $inventory->sales_price,
            'basicPrice'           => $inventory->basic_price,
            'monthlyPrice'         => $inventory->monthly_payment,
            'monthlyRate'          => $inventory->getAttributeById(Attribute::MONTHLY_PRICE, 0),
            'existingPrice'        => $inventory->existing_price,
            'condition'            => $inventory->condition,
            'length'               => $inventory->length,
            'width'                => $inventory->width,
            'height'               => $inventory->height,
            'weight'               => $inventory->weight,
            'gvwr'                 => $inventory->gvwr,
            'axleCapacity'         => $inventory->axle_capacity,
            'payloadCapacity'      => $inventory->payload_capacity,
            'costOfUnit'           => $inventory->cost_of_unit,
            'costOfShipping'       => $inventory->cost_of_shipping,
            'costOfPrep'           => $inventory->cost_of_prep,
            'totalOfCost'          => $inventory->total_of_cost,
            'minimumSellingPrice'  => $inventory->minimum_selling_price,
            'notes'                => $inventory->notes,
            'showOnKsl'            => $inventory->show_on_ksl,
            'showOnRacingjunk'     => $inventory->show_on_racingjunk,
            'showOnWebsite'        => $inventory->show_on_website,
            'videoEmbedCode'       => $inventory->video_embed_code,
            'numAc'                => $inventory->getAttributeById(Attribute::AIR_CONDITIONERS),
            'numAxles'             => $inventory->getAttributeById(Attribute::AXLES),
            'numBatteries'         => $inventory->getAttributeById(Attribute::NUMBER_BATTERIES),
            'numPassengers'        => $inventory->getAttributeById(Attribute::PASSENGERS),
            'numSleeps'            => $inventory->getAttributeById(Attribute::SLEEPING_CAPACITY),
            'numSlideouts'         => $inventory->getAttributeById(Attribute::SLIDEOUTS),
            'numStalls'            => $inventory->getAttributeById(Attribute::STALLS),
            'conversion'           => $inventory->getAttributeById(Attribute::CONVERSION),
            'customConversion'     => $inventory->getAttributeById(Attribute::CUSTOM_CONVERSION),

            // not sure why ES worker wanted to index `shortwallFt` field using zero when it is not setup
            // so lets remain it as it is
            'shortwallFt'          => $inventory->getAttributeById(Attribute::SHORTWALL_LENGTH, 0),
            'color'                => $inventory->getAttributeById(Attribute::COLOR),
            'pullType'             => $inventory->getAttributeById(Attribute::PULL_TYPE),
            'noseType'             => $inventory->getAttributeById(Attribute::NOSE_TYPE),
            'roofType'             => $inventory->getAttributeById(Attribute::ROOF_TYPE),
            'loadType'             => $inventory->getAttributeById(Attribute::CONFIGURATION),
            'fuelType'             => $inventory->getAttributeById(Attribute::FUEL_TYPE),
            'frameMaterial'        => $inventory->getAttributeById(Attribute::CONSTRUCTION),
            'horsepower'           => $inventory->getAttributeById(Attribute::HORSE_POWER),
            'hasLq'                => $inventory->getAttributeById(Attribute::LIVING_QUARTERS),
            'hasManger'            => $inventory->getAttributeById(Attribute::MANGER),
            'hasMidtack'           => $inventory->getAttributeById(Attribute::MIDTACK),
            'hasRamps'             => $inventory->getAttributeById(Attribute::RAMPS),

            // we cannot use $inventory->mileage, its default value is an empty space which we dont want to index by,
            // probably when that Eloquent accessor is fixed, then we could use it
            'mileage'              => $inventory->getAttributeById(Attribute::MILEAGE),
            'mileageMiles'         => $inventory->mileage_miles,
            'mileageKilometres'    => $inventory->mileage_kilometres,
            'isRental'             => $inventory->getAttributeById(Attribute::IS_RENTAL),
            'weeklyPrice'          => $inventory->getAttributeById(Attribute::WEEKLY_PRICE),
            'dailyPrice'           => $inventory->getAttributeById(Attribute::DAILY_PRICE),
            'floorplan'            => $inventory->getAttributeById(Attribute::FLOORPLAN), // maybe this field is deprecated

            // the following fields are not being considered by the ES worker to be pulled,
            // however they are defined in the index map, so we will index them again
            'cabType'              => $inventory->getAttributeById(Attribute::CAB_TYPE),
            'engineSize'           => $inventory->getAttributeById(Attribute::ENGINE_SIZE),
            'transmission'         => $inventory->getAttributeById(Attribute::TRANSMISSION),
            'driveTrail'           => $inventory->getAttributeById(Attribute::DRIVE_TRAIL),
            'propulsion'           => $inventory->getAttributeById(Attribute::PROPULSION),
            'draft'                => $inventory->getAttributeById(Attribute::DRAFT),
            'transom'              => $inventory->getAttributeById(Attribute::TRANSOM),
            'deadRise'             => $inventory->getAttributeById(Attribute::DEAD_RISE),
            'totalWeightCapacity'  => $inventory->getAttributeById(Attribute::TOTAL_WEIGHT_CAPACITY),
            'wetWeight'            => $inventory->getAttributeById(Attribute::WET_WEIGHT),
            'seatingCapacity'      => $inventory->getAttributeById(Attribute::SEATING_CAPACITY),
            'hullType'             => $inventory->getAttributeById(Attribute::HULL_TYPE),
            'engineHours'          => $inventory->getAttributeById(Attribute::ENGINE_HOURS),
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
            'numberAwnings'        => $inventory->getAttributeById(Attribute::NUMBER_AWNINGS),
            'awningSize'           => $inventory->getAttributeById(Attribute::NUMBER_AWNINGS),
            'axleWeight'           => $inventory->getAttributeById(Attribute::AXLE_WEIGHT),
            'engine'               => $inventory->getAttributeById(Attribute::ENGINE),
            'fuelCapacity'         => $inventory->getAttributeById(Attribute::FUEL_CAPACITY),
            'sideWallHeight'       => $inventory->getAttributeById(Attribute::SIDE_WALL_HEIGHT),
            'externalLink'         => $inventory->getAttributeById(Attribute::SIDE_WALL_HEIGHT),
            'subtitle'             => $inventory->getAttributeById(Attribute::SUBTITLE),
            'overallLength'        => $inventory->getAttributeById(Attribute::OVERALL_LENGTH),
            'minWidth'             => $inventory->getAttributeById(Attribute::MIN_WIDTH),
            'minHeight'            => $inventory->getAttributeById(Attribute::MIN_HEIGHT),
            'monthlyPrice2'        => $inventory->getAttributeById(Attribute::MONTHLY_PRICE),

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
                'lat' => $geolocation->latitude, // coordinate name must remain
                'long' => $geolocation->longitude, // coordinate name must remain
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
            'widthInches'          => $inventory->width_inches,
            'heightInches'         => $inventory->height_inches,
            'lengthInches'         => $inventory->length_inches,
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
