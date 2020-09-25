<?php

namespace App\Rules\Website\Forms;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Website\Forms\FieldMap;

class ValidMapTable implements Rule
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
        return in_array($value, FieldMap::getUniqueMapTables());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Field Map Table needs to be: ' . implode(", ", FieldMap::getUniqueMapTables());                
    }
}