<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\Models\Inventory\Inventory;
use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Marketing\Facebook\Listings;
use App\Traits\MarkdownHelper;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Illuminate\Support\Facades\Log;

/**
 * Class InventoryFacebook
 *
 * @package App\Services\Dispatch\Facebook\DTOs
 */
class InventoryFacebook
{
    use MarkdownHelper, WithConstructor, WithGetter;


    /**
     * @const Account Type User
     */
    const ACCOUNT_USER = 'user';

    /**
     * @const Account Type Page
     */
    const ACCOUNT_PAGE = 'page';


    /**
     * @const Listing Vehicle
     */
    const LISTING_VEHICLE = 'vehicle';

    /**
     * @const Listing Item
     */
    const LISTING_ITEM = 'item';

    /**
     * @const Listing Map
     */
    const LISTING_MAP = [
        1 => self::LISTING_VEHICLE,
        2 => self::LISTING_VEHICLE,
        3 => self::LISTING_VEHICLE,
        4 => self::LISTING_VEHICLE,
        5 => self::LISTING_VEHICLE,
        6 => self::LISTING_VEHICLE,
        7 => self::LISTING_VEHICLE,
        8 => self::LISTING_VEHICLE,
        9 => self::LISTING_VEHICLE,
    ];


    /**
     * @const Specific Type Car
     */
    const SPECIFIC_CAR = 'car';

    /**
     * @const Specific Type Trailer
     */
    const SPECIFIC_TRAILER = 'trailer';

    /**
     * @const Specific Type RV
     */
    const SPECIFIC_RV = 'rv';

    /**
     * @const Specific Type Boat
     */
    const SPECIFIC_BOAT = 'boat';

    /**
     * @const Specific Type Commercial
     */
    const SPECIFIC_COMMERCIAL = 'commercial';

    /**
     * @const Specific Type Motorcycle
     */
    const SPECIFIC_MOTORCYCLE = 'motorcycle';

    /**
     * @const Specific Type Power
     */
    const SPECIFIC_POWER = 'powersport';

    /**
     * @const Specific Other
     */
    const SPECIFIC_OTHER = 'other';

    /**
     * @const Specific Entity Map
     */
    const SPECIFIC_ENTITY_MAP = [
        1 => self::SPECIFIC_TRAILER,
        2 => self::SPECIFIC_TRAILER,
        3 => self::SPECIFIC_RV,
        5 => self::SPECIFIC_BOAT,
        6 => self::SPECIFIC_COMMERCIAL,
        7 => self::SPECIFIC_COMMERCIAL,
        8 => self::SPECIFIC_POWER,
        9 => self::SPECIFIC_COMMERCIAL
    ];

    /**
     * @const Specific Type Map
     */
    const SPECIFIC_TYPE_MAP = [
        'vehicle_car' => self::SPECIFIC_CAR,
        'vehicle_motorcycle' => self::SPECIFIC_MOTORCYCLE,
        'vehicle_truck' => self::SPECIFIC_CAR,
        'vehicle_suv' => self::SPECIFIC_CAR,
        'vehicle_semi_truck' => self::SPECIFIC_COMMERCIAL
    ];


    /**
     * @const Default Color
     */
    const COLOR_DEFAULT = 'White';

    /**
     * @const Facebook External Colors
     */
    const COLOR_EXTERNAL = [
        'Black',
        'Blue',
        'Brown',
        'Gold',
        'Green',
        'Gray',
        'Pink',
        'Purple',
        'Red',
        'Silver',
        'Orange',
        'White',
        'Yellow',
        'Charcoal',
        'Off white',
        'Tan',
        'Beige',
        'Burgundy',
        'Turquoise'
    ];

    /**
     * @const Facebook Internal Colors
     */
    const COLOR_INTERNAL = self::COLOR_EXTERNAL;

    /**
     * @const array External Color Map
     */
    const COLOR_EXTERNAL_MAP = [
        'aluminum' => 'Silver',
        'beige' => 'Beige',
        'bronze' => 'Orange',
        'black' => 'Black',
        'blue' => 'Blue',
        'brown' => 'Brown',
        'burgundy' => 'Burgundy',
        'champagne' => 'Orange',
        'charcoal' => 'Charcoal',
        'gold' => 'Gold',
        'gray' => 'Gray',
        'green' => 'Green',
        'matte_black' => 'Black',
        'metallic_gray' => 'Charcoal',
        'metallic_mocha' => 'Brown',
        'mocha' => 'Brown',
        'natural' => 'Brown',
        'orange' => 'Orange',
        'pewter' => 'Silver',
        'pink' => 'Pink',
        'purple' => 'Purple',
        'red' => 'Red',
        'silver' => 'Silver',
        'tan' => 'Tan',
        'two_tone' => 'Off White',
        'white' => 'White',
        'yellow' => 'Yellow',
        'light_blue_metallic' => 'Turquoise',
        'royal_red_metallic' => 'Red',
        'chestnut_metallic' => 'Burgundy',
        'ingot_silver_metallic' => 'Silver',
        'dark_blue_metallic' => 'Blue',
        'sterling_gray_metallic' => 'Gray',
        'birch' => 'Tan'
    ];

    /**
     * @const array Internal Color Map
     */
    const COLOR_INTERNAL_MAP = self::COLOR_EXTERNAL_MAP;


    /**
     * @const string Body Other
     */
    const CAR_BODY_OTHER = 'Other';

    /**
     * @const array Body Styles for Cars
     */
    const CAR_BODY_STYLE = [
        'Coupe',
        'Truck',
        'Sedan',
        'Hatchback',
        'SUV',
        'Convertible',
        'Wagon',
        'Minivan',
        'Small Car',
        'Other'
    ];

    /**
     * @const Array Body Styles Map
     */
    const CAR_BODY_MAP = [
        'hatchback' => 'Hatchback',
        'sedan' => 'Sedan',
        'muvsuv' => 'SUV',
        'coupe' => 'Coupe',
        'convertible' => 'Convertible',
        'wagon' => 'Wagon',
        'van' => 'Minivan',
        'jeep' =>  'SUV'
    ];


    /**
     * @const array Vehicle Conditions
     */
    const VEHICLE_CONDITION = [
        'Excellent',
        'Very Good',
        'Good',
        'Fair',
        'Poor'
    ];


    /**
     * @const array Vehicle Transmission Types
     */
    const VEHICLE_TRANSMISSION = [
        'manual' => 'Manual transmission',
        'automatic' => 'Automatic transmission'
    ];

    /**
     * @const string Default Transmission
     */
    const DEFAULT_TRANSMISSION = 'Manual transmission';


    /**
     * @const array Vehicle Fuel Types
     */
    const VEHICLE_FUEL_TYPE = [
        'Diesel',
        'Electric',
        'Gasoline',
        'Flex',
        'Hybrid',
        'Petrol',
        'Plug-in Hybrid',
        'Other'
    ];

    /**
     * @const array Vehicle Fuel Type Map
     */
    const VEHICLE_FUEL_MAP = [
        'gas' => 'Gasoline',
        'electric' => 'Electric',
        'diesel' => 'Diesel',
        'flex' => 'Flex'
    ];

    /**
     * @const string Vehicle Fuel Type Other
     */
    const VEHICLE_FUEL_OTHER = 'Other';


    /**
     * @var int
     */
    private $inventoryId;

    /**
     * @var int
     */
    private $facebookId;

    /**
     * @var string
     */
    private $accountType;

    /**
     * @var int
     */
    private $pageUrl;

    /**
     * @var int
     */
    private $pageId;

    /**
     * @var string
     */
    private $entityTypeId;

    /**
     * @var string
     */
    private $category;

    /**
     * @var int
     */
    private $year;

    /**
     * @var float
     */
    private $price;

    /**
     * @var string
     */
    private $manufacturer;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $dealerLocationId;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $color;

    /**
     * @var int
     */
    private $mileage;

    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $condition;

    /**
     * @var string
     */
    private $transmission;

    /**
     * @var string
     */
    private $fuelType;

    /**
     * @var Collection<InventoryImage>
     */
    private $images;


    /**
     * Create InventoryFacebook From Inventory
     *
     * @param Inventory $inventory
     * @param Marketplace $integration
     * @return InventoryFacebook
     */
    public static function getFromInventory(Inventory $inventory,
            Marketplace $integration): InventoryFacebook
    {
        $calculatedPrice = (!is_null($inventory->sales_price) && $inventory->sales_price > 0) ? $inventory->sales_price : ($inventory->use_website_price && $inventory->website_price > 0 ? $inventory->website_price : $inventory->price);
        // Create Inventory Mapping
        return new self([
            'inventory_id' => $inventory->inventory_id,
            'page_url' => $integration->page_url,
            'entity_type_id' => $inventory->entity_type_id,
            'category' => $inventory->category,
            'price' => intval($calculatedPrice),
            'year' => $inventory->year,
            'manufacturer' => $inventory->manufacturer,
            'model' => $inventory->model,
            'description' => (strlen($inventory->description) < INVENTORY::MIN_DESCRIPTION_LENGTH_FOR_FACEBOOK) ? strip_tags($inventory->description_html) : $inventory->description,
            'dealer_location_id' => $inventory->dealer_location_id,
            'location' => $inventory->dealerLocation->city_state,
            'color' => $inventory->attributes->get('color'),
            'mileage' => $inventory->attributes->get('mileage'),
            'body' => $inventory->attributes->get('body'),
            'transmission' => $inventory->attributes->get('transmission'),
            'fuel_type' => $inventory->attributes->get('fuel_type'),
            'images' => $inventory->orderedImages
        ]);
    }

    /**
     * Create InventoryFacebook From Inventory
     *
     * @param Listings $listing
     * @return InventoryFacebook
     */
    public static function getFromListings(Listings $listing): InventoryFacebook
    {
        // Create Inventory Mapping
        $inventory = $listing->inventory ?? null;
        return new self([
            'inventory_id' => $inventory ? $inventory->inventory_id : $listing->inventory_id,
            'facebook_id' => $listing->facebook_id,
            'page_url' => $listing->marketplace ? $listing->marketplace->page_url : '',
            'entity_type_id' => $inventory ? $inventory->entity_type_id : null,
            'category' => $inventory ? $inventory->category : null,
            'price' => $inventory ? $inventory->price : null,
            'year' => $inventory ? $inventory->year : null,
            'manufacturer' => $inventory ? $inventory->manufacturer : null,
            'model' => $inventory ? $inventory->model : null,
            'description' => $inventory ? $inventory->description : null,
            'dealer_location_id' => $inventory ? $inventory->dealer_location_id : null,
            'location' => $inventory ? $inventory->dealerLocation->city_region : null,
            'color' => $inventory ? $inventory->attributes->get('color') : null,
            'mileage' => $inventory ? $inventory->attributes->get('mileage') : null,
            'body' => $inventory ? $inventory->attributes->get('body') : null,
            'transmission' => $inventory ? $inventory->attributes->get('transmission') : null,
            'fuel_type' => $inventory ? $inventory->attributes->get('fuel_type') : null,
            'images' => $inventory ? $inventory->orderedImages : null
        ]);
    }


    /**
     * Get Description With Markdown Conversion
     *
     * @return string
     */
    public function getMarkdownDescription(): string {
        return $this->convertMarkdown($this->description);
    }

    /**
     * Get Description With HTML Removed
     *
     * @return string
     */
    public function getPlainDescription(): string {
        return $this->stripMarkdown($this->description ?? '');
    }

    /**
     * Get Account Type
     *
     * @return string
     */
    public function getAccountType(): string {
        // Page URL Exists?
        if($this->pageUrl) {
            // Type is Page
            return self::ACCOUNT_PAGE;
        } else {
            // Type is User
            return self::ACCOUNT_USER;
        }
    }

    /**
     * Get Listing Type
     *
     * @return string
     */
    public function getListingType(): string {
        // Return Listing Type
        return self::LISTING_MAP[$this->entityTypeId] ?? self::LISTING_VEHICLE;
    }

    /**
     * Get Specific Type
     *
     * @return string
     */
    public function getSpecificType(): string {
        // Entity Exists?
        if(!empty(self::SPECIFIC_ENTITY_MAP[$this->entityTypeId])) {
            return self::SPECIFIC_ENTITY_MAP[$this->entityTypeId];
        }

        // Return Specific Type
        return self::SPECIFIC_TYPE_MAP[$this->category] ?? self::SPECIFIC_OTHER;
    }


    /**
     * Get Color
     *
     * @param bool $internal
     * @return string
     */
    public function getColor(bool $internal = false): string {
        // Get External
        if(!$internal) {
            return self::COLOR_EXTERNAL_MAP[$this->color] ?? self::COLOR_DEFAULT;
        }

        // Get Internal
        return self::COLOR_INTERNAL_MAP[$this->color] ?? self::COLOR_DEFAULT;
    }

    /**
     * Get Body Style
     *
     * @return string
     */
    public function getBodyStyle(): string {
        // Get Internal
        return self::CAR_BODY_MAP[$this->body] ?? self::CAR_BODY_OTHER;
    }

    /**
     * Get Condition
     *
     * @return string
     */
    public function getCondition(): string {
        // Get Default Condition
        return self::VEHICLE_CONDITION[0];
    }

    /**
     * Get Transmission
     *
     * @return string
     */
    public function getTransmission(): string {
        // Get Transmission
        return self::VEHICLE_TRANSMISSION[$this->transmission] ?? self::DEFAULT_TRANSMISSION;
    }

    /**
     * Get Fuel Type
     *
     * @return string
     */
    public function getFuelType(): string {
        // Get Fuel Type
        return self::VEHICLE_FUEL_MAP[$this->fuelType] ?? self::VEHICLE_FUEL_OTHER;
    }
}
