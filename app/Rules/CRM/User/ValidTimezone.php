<?php

namespace App\Rules\CRM\User;

use Illuminate\Contracts\Validation\Rule;

class ValidTimezone implements Rule
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
        return in_array($value, timezone_identifiers_list());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid Timezone String';
    }
}