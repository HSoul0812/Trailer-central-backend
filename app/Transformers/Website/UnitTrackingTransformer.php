<?php

namespace App\Transformers\Website;

use App\Models\Website\Tracking\TrackingUnit;
use App\Transformers\Inventory\InventoryTransformer;
use League\Fractal\TransformerAbstract;

class UnitTrackingTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'inventory'
    ];
    
    public function transform(TrackingUnit $trackingUnit) 
    {
        return [];
    }
    
    public function includeInventory(TrackingUnit $trackingUnit)
    {
        return $this->item($trackingUnit->inventory, new InventoryTransformer());
    }
}
