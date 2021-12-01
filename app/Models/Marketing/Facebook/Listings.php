<?php

namespace App\Models\Marketing\Facebook;

use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Marketing\Facebook\Image;
use App\Models\Inventory\Inventory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Listings
 * 
 * @package App\Models\Marketing\Facebook\Listings
 */
class Listings extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_listings';


    /**
     * @const array Two-Factor Auth Types
     */
    const TFA_TYPES = [
        'authy' => 'Authy'
    ];

    /**
     * @const array Listing Types
     */
    const LISTING_TYPES = [
        'item' => 'Item for Sale',
        'vehicle' => 'Vehicle for Sale',
        'home' => 'Home for Sale or Rent',
        'job' => 'Job Opening'
    ];

    /**
     * @const array Vehicle Types
     */
    const SPECIFIC_TYPES = [
        'vehicle' => [
            'car' => 'Car/Truck',
            'motorcycle' => 'Motorcycle',
            'powersport' => 'Powersport',
            'rv' => 'RV/Camper',
            'trailer' => 'Trailer',
            'boat' => 'Boat',
            'commercial' => 'Commercial/Industrial',
            'other' => 'Other'
        ]
    ];

    /**
     * @const array Listing Statuses
     */
    const STATUSES = [
        'active',
        'deleted',
        'expired'
    ];


    /**
     * @const Facebook External Colors
     */
    const FB_COLOR_EXTERNAL = [
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
    const FB_COLOR_INTERNAL = self::FB_COLOR_EXTERNAL;

    /**
     * @const array External Color Map
     */
    const MAP_COLOR_EXTERNAL = [
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
        'two_tone' => 'White',
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
    const MAP_COLOR_INTERNAL = self::MAP_COLOR_EXTERNAL;


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
        'Manual transmission',
        'Automatic transmission'
    ];

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
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'marketplace_id',
        'inventory_id',
        'facebook_id',
        'account_type',
        'page_id',
        'listing_type',
        'specific_type',
        'year',
        'price',
        'make',
        'model',
        'description',
        'location',
        'color_exterior',
        'color_interior',
        'trim',
        'mileage',
        'body_style',
        'condition',
        'transmission',
        'fuel_type',
        'status'
    ];

    /**
     * Get Marketplace Integration
     * 
     * @return BelongsTo
     */
    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class, 'marketplace_id', 'id');
    }

    /**
     * Get Inventory
     * 
     * @return BelongsTo
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }

    /**
     * Get Images
     * 
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'id', 'listing_id');
    }


    /**
     * Get All Specific Listing Types
     * 
     * @return array<string>
     */
    public static function getAllSpecificTypes(): array
    {
        // Initialize Array
        $allSpecificTypes = array();

        // Get All Specific Types for Facebook Marketplace
        foreach(self::SPECIFIC_TYPES as $listingType => $specificTypes) {
            foreach($specificTypes as $index => $value) {
                $allSpecificTypes[] = $index;
            }
        }

        // Return Array
        return $allSpecificTypes;
    }
}