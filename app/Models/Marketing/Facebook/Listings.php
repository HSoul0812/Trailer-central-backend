<?php

namespace App\Models\Marketing\Facebook;

use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Marketing\Facebook\Image;
use App\Models\Inventory\Inventory;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Listings
 * 
 * @package App\Models\Marketing\Facebook\Listings
 */
class Listings extends Model
{
    use TableAware;


    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_listings';


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
        'invalid',
        'failed',
        'deleted',
        'expired'
    ];

    /**
     * @const array Listing Status Active
     */
    const STATUS_ACTIVE = 'active';

    /**
     * @const array Listing Status Deleted
     */
    const STATUS_DELETED = 'deleted';

    /**
     * @const array Listing Status Expired
     */
    const STATUS_EXPIRED = 'expired';


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
        'username',
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
        return $this->hasMany(Image::class, 'listing_id', 'id');
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