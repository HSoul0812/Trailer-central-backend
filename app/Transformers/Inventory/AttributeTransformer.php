<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\Attribute;
use League\Fractal\TransformerAbstract;

/**
 * Class AttributeTransformer
 * @package App\Transformers\Inventory
 */
class AttributeTransformer extends TransformerAbstract
{
    /**
     * @param Attribute $attribute
     * @return array
     */
    public function transform(Attribute $attribute): array
    {
        return [
            'attribute_id' => $attribute->attribute_id,
            'code' => $attribute->code,
            'name' => $attribute->name,
            'type' => $attribute->type,
            'values' => $attribute->values,
            'extra_values' => $attribute->extra_values,
            'description' => $attribute->description,
            'default_value' => $attribute->default_value,
            'aliases' => $attribute->aliases,
        ];
    }
}
