<?php

namespace App\Models\Integration\Facebook;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Integration\Auth\AccessToken;

/**
 * Class Catalog
 * @package App\Models\Integration\Facebook
 */
class Catalog extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_catalog';

    /**
     * Define Catalog URL Prefix
     */
    const CATALOG_URL_PREFIX = 'facebook/catalog';

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
        'account_name',
        'account_id',
        'feed_id',
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
     * Get Feed Path
     * 
     * @return string of calculated feed path
     */
    public function getFeedPathAttribute()
    {
        return '/' . self::CATALOG_URL_PREFIX . '/' . $this->account_id . '/' . $this->page->page_id . '.csv';
    }

    /**
     * Get Feed Url
     * 
     * @return string of calculated feed url
     */
    public function getFeedUrlAttribute()
    {
        return $_ENV['AWS_URL'] . '/' . $_ENV['AWS_BUCKET'] . '/' . self::CATALOG_URL_PREFIX . '/' . $this->account_id . '/' . $this->page->page_id . '.csv';
    }

    /**
     * Get Feed Name
     * 
     * @return string of calculated feed name
     */
    public function getFeedNameAttribute()
    {
        return $this->account_name . "'s Feed for " . $this->page_title;
    }
}
