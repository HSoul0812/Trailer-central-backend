<?php

namespace App\Models\Parts\Textrail;

use App\Models\Parts\Brand as BaseBrand;
use App\Models\Parts\Textrail\Part;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends BaseBrand
{
    protected $table = 'textrail_brands';
    
    public function parts(): HasMany 
    {
        return $this->hasMany(Part::class);
    }
}
