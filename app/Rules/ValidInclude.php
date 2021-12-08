<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class ValidInclude
 * @package App\Rules
 */
class ValidInclude implements Rule
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
        $valueArr = explode(',', $value);

        return count(array_diff($valueArr, $parameters)) === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute is not valid.';
    }
}
