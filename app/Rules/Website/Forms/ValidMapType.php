<?php

namespace App\Rules\CRM\Website\Forms;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Website\Forms\FieldMap;

class ValidMapType implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Value Exists In Field Map Types?
        return isset(FieldMap::MAP_TYPES[$value]);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Field Map type needs to be: ' . implode(", ", array_keys(FieldMap::MAP_TYPES));                
    }
}