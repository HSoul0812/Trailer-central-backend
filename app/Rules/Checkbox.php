<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class Checkbox
 * @package App\Rules
 */
class Checkbox implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array($value, [1, 0, '1', '0', true, false, 'on'], true);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be boolean.';
    }
}
