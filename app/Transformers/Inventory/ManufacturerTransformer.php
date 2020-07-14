<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\InventoryMfg;

class ManufacturerTransformer extends TransformerAbstract {
    
    public function transform(InventoryMfg $manufacturer) {
        return [
            'id' => $manufacturer->id,
            'name' => $manufacturer->name,
            'label' => $manufacturer->label,
        ];
    }
}
