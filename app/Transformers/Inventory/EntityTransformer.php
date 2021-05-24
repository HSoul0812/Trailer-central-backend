<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\EntityType;

class EntityTransformer extends TransformerAbstract {
    
    public function transform(EntityType $entity) {
        return [
            'id' => $entity->entity_type_id,
            'name' => $entity->name,
            'label' => $entity->title
        ];
    }
    
}
