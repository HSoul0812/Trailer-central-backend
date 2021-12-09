<?php

namespace App\Models\Marketing\Facebook;

use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Filter
 * 
 * @package App\Models\Marketing\Facebook\Filter
 */
class Filter extends Model
{
    use TableAware;


    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_marketplace_filters';


    /**
     * @const array Filter Types
     */
    const FILTER_TYPES = [
        'entity' => 'Entity Type',
        'category' => 'Category'
    ];

    /**
     * @const array Filter Columns
     */
    const FILTER_COLUMNS = [
        'entity' => 'entity_type_id',
        'category' => 'category'
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
        'marketplace_id',
        'filter_type',
        'filter'
    ];

    /**
     * Get Marketplace Integration
     * 
     * @return BelongsTo
     */
    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class, 'marketplace_id', 'id');
    }
}