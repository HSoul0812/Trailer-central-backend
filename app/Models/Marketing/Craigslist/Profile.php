<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Marketing\Craigslist\City;
use App\Models\Marketing\Craigslist\ClCity;
use App\Models\Marketing\Craigslist\Subarea;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Profile
 * 
 * @package App\Models\Marketing\Craigslist
 */
class Profile extends Model
{
    use TableAware;


    /**
     * @const array{string: string}
     */
    const MAP_GROUPING = [
        'fso' => 'o',
        'fsd' => 'd',
        'ho' => 'ho'
    ];


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_profile';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'dealer_location_id',
        'location_filter',
        'username',
        'password',
        'profile',
        'phone',
        'location',
        'postal',
        'city',
        'city_location',
        'postCategory',
        'postingInterval',
        'cl_privacy',
        'image_limit',
        'renew_interval',
        'use_map',
        'map_street',
        'map_cross_street',
        'map_city',
        'map_state',
        'format_dbk',
        'format_dfbk',
        'format_fbk',
        'show_more_ads',
        'autoposting_enable',
        'autoposting_items',
        'autoposting_hrs',
        'autoposting_slot_id',
        'autoposting_start_at',
        'embed_phone',
        'embed_dealer_name',
        'embed_dealer_and_phone',
        'embed_logo',
        'embed_logo_position',
        'embed_logo_width',
        'embed_logo_height',
        'embed_upper',
        'embed_bg_upper',
        'embed_text_upper',
        'embed_lower',
        'embed_bg_lower',
        'embed_text_lower',
        'keywords',
        'scramble',
        'blurb',
        'proxy_type',
        'proxy_host',
        'proxy_port',
        'proxy_user',
        'proxy_pass',
        'sound_notify',
        'use_website_price',
        'market_city',
        'market_subarea',
        'profile_type'
    ];

    /**
     * Get User
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Dealer Location
     * 
     * @return BelongsTo
     */
    public function dealerLocation(): BelongsTo
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }

    /**
     * Get Category
     * 
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'postCategory', 'category');
    }

    /**
     * Get City
     * 
     * @return BelongsTo
     */
    public function cities(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city', 'city');
    }

    /**
     * Get CL City / Area
     * 
     * @return BelongsTo
     */
    public function clCity(): BelongsTo
    {
        return $this->belongsTo(ClCity::class, 'city', 'name');
    }

    /**
     * Get Subarea
     * 
     * @return BelongsTo
     */
    public function subarea(): BelongsTo
    {
        return $this->belongsTo(Subarea::class, 'city_location', 'name');
    }

    /**
     * Get Base URL
     * 
     * @return string
     */
    public function getBaseUrlAttribute(): string {
        // Get Category
        $category = $this->category->abbr;

        // Get Subarea
        $subarea = (!empty($this->subarea->code) ? '/' . $this->subarea->code : '');

        // Get By
        $by = self::MAP_GROUPING[$this->category->grouping];

        // Return Full URL Path
        return $this->cities->url . $category . $subarea . '/' . $by . '/';
    }
}