<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class City
 * 
 * @package App\Models\Marketing\Craigslist
 */
class City extends Model
{
    use TableAware;


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_cities';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'city_id';

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
        'city',
        'location',
        'url'
    ];


    /**
     * Get CL City
     * 
     * @return BelongsTo
     */
    public function clCity(): HasOne
    {
        return $this->belongsTo(ClCity::class, 'city', 'name');
    }


    /**
     * Get City
     * 
     * @return string
     */
    public function getTimezoneAttribute(): string
    {
        return $this->clCity->timezone;
    }
}
