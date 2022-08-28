<?php

namespace App\Models\Parts\Textrail;

use App\Models\Parts\Category as BaseCategory;
use App\Models\Parts\Textrail\Part;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends BaseCategory
{
    protected $table = 'textrail_categories';

    protected $fillable = [
        'name',
        'parent_id'
    ];

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}
