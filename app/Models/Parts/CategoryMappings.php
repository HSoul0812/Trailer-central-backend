<?php

declare(strict_types=1);

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryMappings extends Model
{
    public $timestamps = false;
    protected $table = 'category_mappings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'map_from',
        'map_to',
        'type',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
