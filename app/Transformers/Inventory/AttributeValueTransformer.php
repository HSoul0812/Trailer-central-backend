<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\AttributeValue;
use League\Fractal\TransformerAbstract;
use Str;

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
            'value' => $this->normalize($attributeValue),
            'code' => $attributeValue->attribute->code,
            'name' => $attributeValue->attribute->name,
            'type' => $attributeValue->attribute->type,
        ];
    }

    /**
     * Normalize the attribute value when it is type `select`.
     *
     * Normalize: ensure the value is in lowercase
     *
     * Edge case: we have an attribute `conversion` which was setup with values like
     *            `Bunkhouse of Alabama`, `Compass Conversions`, so we need to avoid breaking changes for it.
     *
     * Justification: All attributes values are lower case by nature, excepts `conversion`, so every single inventory
     *                attribute value should be lowercase, but strangely some values are uppercase (probably an integration issue)
     *
     * @param AttributeValue $value
     * @return string|null
     */
    private function normalize(AttributeValue $value): ?string
    {
        // we could have evaluated by asking for attribute id, but it could be possible in the future
        // someone else add another similar kind of value like `Bunkhouse of Alabama`, so this is a wide fixer.
        if ($value->attribute->isSelect() && !Str::contains($value->value, ' ')) {

            return Str::lower($value->value);
        }

        return $value->value;
    }
}
