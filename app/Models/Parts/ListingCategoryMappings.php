<?php

declare(strict_types=1);

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingCategoryMappings extends Model
{
    public const TYPE_INVENTORY = 'Inventory';

    public const TYPE_ID_GENERAL_TRAILER = 1;

    public const ENTITY_TYPE_ID_TRAILER = 1;

    public $timestamps = false;

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
        'type',
    ];

    public function listingCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
