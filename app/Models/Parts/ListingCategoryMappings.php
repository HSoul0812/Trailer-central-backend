<?php

declare(strict_types=1);

namespace App\Models\Parts;

use App\Support\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingCategoryMappings extends Model
{
    protected $table = 'listing_category_mappings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'name',
        'map_from',
        'map_to',
        'type'
    ];

    public $timestamps = false;

    public function listingCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

}
