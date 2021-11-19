<?php

namespace App\Models\Marketing\Facebook;

use App\Models\User\User;
use App\Models\User\DealerLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Marketplace
 * 
 * @package App\Models\Marketing\Facebook\Marketplace
 */
class Marketplace extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_marketplace';


    /**
     * @const array Account Types
     */
    const ACCOUNT_TYPES = [
        'page',
        'user'
    ];

    /**
     * @const array Two-Factor Auth Types
     */
    const TFA_TYPES = [
        'authy' => 'Authy'
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
        'dealer_id',
        'dealer_location_id',
        'page_url',
        'fb_username',
        'fb_password',
        'tfa_username',
        'tfa_password',
        'tfa_type'
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
     * Get Listings
     * 
     * @return HasMany
     */
    public function listings(): HasMany
    {
        return $this->hasMany(DealerLocation::class, 'id', 'marketplace_id');
    }
}