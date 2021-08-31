<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\CustomOverlay;

class CustomOverlayTransformer extends TransformerAbstract {
    
    public function transform(CustomOverlay $customOverlay) {
        return [
            'id' => $customOverlay->id,
            'name' => $customOverlay->name,
            'value' => $customOverlay->value
        ];
    }
    
}
