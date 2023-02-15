<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Market
 *
 * @package App\Models\Marketing\Craigslist
 */
class Market extends Model
{
    use TableAware;


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_markets';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var array<string>
     */
    protected $primaryKey = ['city_code', 'subarea_code'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'city_code',
        'city_name',
        'city_alt_name',
        'city_domain',
        'subarea_code',
        'subarea_name',
        'subarea_alt_name'
    ];
}
