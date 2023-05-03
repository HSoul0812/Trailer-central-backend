<?php

declare(strict_types=1);

namespace App\Models\Parts;

use App\Support\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    use TableAware;
    protected $table = 'part_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
       'pivot',
     ];

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(Type::class, 'part_category_part_type', 'part_category_id', 'part_type_id')->select('id', 'name');
    }

    /**
     * Get the image associated with the category.
     */
    public function image(): HasOne
    {
        return $this->hasOne(CategoryImage::class)->select('id', 'image_url', 'category_id');
    }

    /**
     * Get the category mapping associated with the category.
     */
    public function category_mappings(): HasOne
    {
        return $this->hasOne(CategoryMappings::class);
    }
}
