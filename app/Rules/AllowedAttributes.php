<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class AllowedAttributes
 * @package App\Rules
 */
class AllowedAttributes implements Rule
{
    /**
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return false;
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validate($attribute, $value, $parameters): bool
    {
        return in_array($attribute, $parameters);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attributes are not allowed.';
    }
}
