<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Category
 * 
 * @package App\Models\Marketing\Craigslist
 */
class Category extends Model
{
    use TableAware;


    /**
     * @const string
     */
    const GROUP_BY_DEALER = 'fsd';

    /**
     * @const string
     */
    const GROUP_BY_OWNER = 'fso';

    /**
     * @const string
     */
    const GROUP_HOUSING = 'ho';


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_categories';

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
        'category',
        'display',
        'grouping',
        'abbr',
        'type',
        'price',
        'sort_order',
        'visible',
        'visible_parts'
    ];
}
