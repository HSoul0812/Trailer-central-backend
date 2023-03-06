<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClCity
 * 
 * @package App\Models\Marketing\Craigslist
 */
class ClCity extends Model
{
    use TableAware;


    /**
     * @const array<string> Define Country Codes
     */
    const COUNTRY_CODES = [
        'US',
        'CA'
    ];


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_cl_city';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'code';

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
        'code',
        'name',
        'alt_name',
        'preview_name',
        'domain',
        'timezone'
    ];


    /**
     * Get City
     * 
     * @return BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'name', 'city');
    }


    /**
     * Get URL Attribute
     * 
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return $this->city->url;
    }
}