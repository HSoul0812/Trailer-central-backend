<?php

namespace App\Rules\CRM\Website\Forms;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Website\Forms\FieldMap;

class ValidMapField implements Rule
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
        return in_array($value, array_keys(FieldMap::getNovaMapFields()));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Field Map Form Field needs to be: ' . implode(", ", array_keys(FieldMap::getNovaMapFields()));                
    }
}