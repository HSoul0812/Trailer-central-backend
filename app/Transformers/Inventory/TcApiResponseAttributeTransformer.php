<?php

namespace App\Transformers\Inventory;

use App\DTOs\Inventory\TcApiResponseAttribute;
use League\Fractal\TransformerAbstract;

class TcApiResponseAttributeTransformer extends TransformerAbstract
{
    public function transform(TcApiResponseAttribute $type): array
    {
        return [
            'attribute_id' => $type->attribute_id,
            'code' => $type->code,
            'name' => $type->name,
            'type' => $type->type,
            'values' => $type->values,
            'extra_values' => $type->extra_values,
            'description' => $type->description,
            'default_value' => $type->default_value,
            'aliases' => $type->aliases,
        ];
    }
}
