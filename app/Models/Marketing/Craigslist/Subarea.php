<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Subarea
 * 
 * @package App\Models\Marketing\Craigslist
 */
class Subarea extends Model
{
    use TableAware;


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_cl_subarea';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var array<string>
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
        'city_code',
        'name',
        'alt_name',
        'preview_name'
    ];
}