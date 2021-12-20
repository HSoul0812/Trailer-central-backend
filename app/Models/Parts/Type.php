<?php

declare(strict_types=1);

namespace App\Models\Parts;

use App\Support\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Type extends Model
{
    use TableAware;
    protected $table = 'part_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
      'pivot',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'part_category_part_type', 'part_category_id', 'part_type_id')->select('id', 'name');
    }
    
    /**
     * Get the image associated with the type.
     */
    public function image(): HasOne
    {
        return $this->hasOne(TypeImage::class);
    }
}
