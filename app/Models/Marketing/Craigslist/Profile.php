<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\User\User;
use App\Models\User\DealerLocation;
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
        'autposting_items',
        'autposting_hrs',
        'autposting_slot_id',
        'autoposting_start_at',
        'embed_phone',
        'embed_dealer_phone',
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
}
