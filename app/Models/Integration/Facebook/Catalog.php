<?php

namespace App\Models\Integration\Facebook;

use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Integration\Auth\AccessToken;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Catalog
 * @package App\Models\Integration\Facebook
 */
class Catalog extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_catalog';


    /**
     * @const array Catalog Types
     */
    const CATALOG_TYPES = [
        'commerce',
        'hotels',
        'flights',
        'destinations',
        'home_listings',
        'vehicles',
        'vehicle_offers'
    ];

    /**
     * @const array Vehicle Type
     */
    const VEHICLE_TYPE = 'vehicles';

    /**
     * @const array Home Type
     */
    const HOME_TYPE = 'home_listings';

    /**
     * @const array Product Type
     */
    const PRODUCT_TYPE = 'products';

    /**
     * @const string Default Catalog Type
     */
    const DEFAULT_TYPE = 'vehicles';


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
        'dealer_id',
        'dealer_location_id',
        'fbapp_page_id',
        'business_id',
        'catalog_id',
        'catalog_name',
        'catalog_type',
        'account_name',
        'account_id',
        'filters',
        'is_active'
    ];

    /**
     * Get User
     * 
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Dealer Location
     * 
     * @return BelongsTo
     */
    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }

    /**
     * Get Page
     * 
     * @return BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'fbapp_page_id', 'id');
    }

    /**
     * Get Feed
     * 
     * @return BelongsTo
     */
    public function feed()
    {
        return $this->belongsTo(Feed::class, 'catalog_id', 'catalog_id');
    }

    /**
     * Access Token
     * 
     * @return HasOne
     */
    public function accessToken()
    {
        return $this->hasOne(AccessToken::class, 'relation_id', 'id')
                    ->whereTokenType('facebook')
                    ->whereRelationType('fbapp_catalog');
    }


    /**
     * Get Catalog Name or ID
     *
     * @return string
     */
    public function getCatalogNameIdAttribute(): string
    {
        // Catalog Name Exists?
        if(!empty($this->catalog_name)) {
            return $this->catalog_name;
        }

        // Return Catalog ID
        return !empty($this->catalog_id) ? '#' . $this->catalog_id : '';
    }
}
