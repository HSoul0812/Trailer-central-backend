<?php

namespace App\Models\Marketing\Facebook;

use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Marketing\Facebook\Filter;
use App\Models\Traits\TableAware;
use Carbon\Carbon;
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
    use TableAware;


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
        'authy' => 'Authy',
        'sms' => 'SMS'
    ];

    /**
     * @const array Active Two-Factor Auth Types
     */
    const TFA_TYPES_ACTIVE = [
        'sms'
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
        'tfa_type',
        'imported_at'
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
        return $this->hasMany(Listings::class, 'marketplace_id', 'id');
    }

    /**
     * Get Filters
     * 
     * @return HasMany
     */
    public function filters(): HasMany
    {
        return $this->hasMany(Filter::class, 'marketplace_id', 'id');
    }


    /**
     * Get Filters Map
     * 
     * @return array{entity: array<string>,
     *               category: array<string>}
     */
    public function getFilterMapAttribute(): array
    {
        // Get Filters Map
        $filters = $this->filters()->get();

        // Loop Filters
        $filtersMap = [];
        foreach($filters as $filter) {
            $type = $filter->filter_type;
            if(!isset($filtersMap[$type])) {
                $filtersMap[$type] = [];
            }
            $filtersMap[$type][] = $filter->filter;
        }

        // Return Filters Map
        return $filtersMap;
    }

    /**
     * Is Up To Date?
     * 
     * @return bool
     */
    public function getIsUpToDateAttribute(): bool
    {
        // Get Last Imported Date
        if(empty($this->imported_at)) {
            return false;
        }

        // Compare Times
        $hours = ((int) config('marketing.fb.settings.limit.hours', 0)) * 60 * 60;
        if(empty($hours)) {
            return false;
        }

        // Check if Hours Limits Exceeded
        return Carbon::parse($this->imported_at, 'UTC')->timestamp > (time() - $hours);
    }
}