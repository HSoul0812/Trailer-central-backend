<?php

namespace App\Rules\User;

use Illuminate\Contracts\Validation\Rule;

class ValidPassword implements Rule
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
        if (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.* ).{8,16}$/", $value)) {
            return false;
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
        return 'Password should be at least 1 Capital letter, 1 Number and min 8 chars.';
    }
}
