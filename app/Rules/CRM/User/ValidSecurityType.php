<?php

namespace App\Rules\CRM\User;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\User\SalesPerson;

class ValidSecurityType implements Rule
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
        // Security Type Exists?
        return !empty($value) && in_array($value, SalesPerson::SECURITY_TYPES);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Security type needs to be: ' . implode(", ", SalesPerson::SECURITY_TYPES);
    }
}