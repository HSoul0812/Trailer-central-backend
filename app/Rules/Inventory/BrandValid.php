<?php

namespace App\Rules\Inventory;

use App\Models\Inventory\EntityType;
use Dompdf\Exception;
use Illuminate\Contracts\Validation\Rule;
use App\Models\Inventory\Manufacturers\Brand;

class BrandValid implements Rule
{
    private const ENTITY_TYPES = [EntityType::ENTITY_TYPE_WATERCRAFT, EntityType::ENTITY_TYPE_RV];

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param array $parameters first one must be the inventory entity_type_id
     *
     * @return bool
     */
    public function passes($attribute, $value, array $parameters = [])
    {
        if (!empty($parameters) && in_array($parameters[0], self::ENTITY_TYPES)) {
            return Brand::where('name', $value)->count() > 0;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute does not exist in the DB.';
    }
}
