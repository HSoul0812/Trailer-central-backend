<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\Manufacturers\Manufacturers;

class ManufacturerTransformer extends TransformerAbstract {
    
    public function transform(Manufacturers $manufacturer) {
        return [
            'id' => $manufacturer->id,
            'name' => $manufacturer->name,
            'label' => $manufacturer->label,
        ];
    }
}
