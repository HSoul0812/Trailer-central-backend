<?php

namespace App\Models\Parts\Textrail;

use App\Models\Parts\Manufacturer as BaseMake;
use App\Models\Parts\Textrail\Part;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manufacturer extends BaseMake
{
     protected $table = 'textrail_manufacturers';
    
    
    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}
