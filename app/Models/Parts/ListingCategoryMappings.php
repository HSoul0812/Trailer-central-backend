<?php

declare(strict_types=1);

namespace App\Models\Parts;

use App\Support\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingCategoryMappings extends Model
{
    const TYPE_INVENTORY = 'Inventory';

    const TYPE_ID_GENERAL_TRAILER = 1;

    const ENTITY_TYPE_ID_TRAILER = 1;

    protected $table = 'listing_category_mappings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_id',
        'map_from',
        'map_to',
        'entity_type_id',
        'type'
    ];

    public $timestamps = false;

    public function listingCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

}
