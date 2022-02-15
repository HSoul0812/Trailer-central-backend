<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\AttributeValue;
use League\Fractal\TransformerAbstract;

/**
 * Class AttributeValueTransformer
 * @package App\Transformers\Inventory
 */
class AttributeValueTransformer extends TransformerAbstract
{
    /**
     * @param AttributeValue $attributeValue
     * @return array
     */
    public function transform(AttributeValue $attributeValue): array
    {
        return [
            'attribute_id' => $attributeValue->attribute->attribute_id,
            'value' => $attributeValue->value,
            'code' => $attributeValue->attribute->code,
            'name' => $attributeValue->attribute->name,
            'type' => $attributeValue->attribute->type,
        ];
    }
}
