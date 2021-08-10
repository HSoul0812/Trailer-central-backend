<?php

namespace App\Transformers\Website;

use App\Models\Website\Tracking\Tracking;
use League\Fractal\TransformerAbstract;
use App\Transformers\Website\UnitTrackingTransformer;

class TrackingTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'inventory'
    ];
    
    public function transform(Tracking $tracking) 
    {
        return [];
    }
    
    public function includeInventory(Tracking $tracking)
    {
        return $this->collection($tracking->units, new UnitTrackingTransformer());
    }
}
