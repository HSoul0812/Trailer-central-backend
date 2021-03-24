<?php

namespace App\Rules\Integration\Auth;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Integration\Auth\AccessToken;

class ValidRelationType implements Rule
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
        // Value Exists In Token Types?
        return in_array($value, array_keys(AccessToken::RELATION_TYPES));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Access Token relation type needs to be: ' . implode(", ", array_keys(AccessToken::RELATION_TYPES));
    }
}