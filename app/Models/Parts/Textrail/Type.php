<?php

namespace App\Models\Parts\Textrail;

use App\Models\Parts\Type as BaseType;
use App\Models\Parts\Textrail\Part;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Type extends BaseType
{
    protected $table = 'textrail_types';

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}
