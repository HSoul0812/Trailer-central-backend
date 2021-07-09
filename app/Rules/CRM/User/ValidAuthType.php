<?php

namespace App\Rules\CRM\User;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\User\SalesPerson;

class ValidAuthType implements Rule
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
        // Auth Type Exists?
        return !empty($value) && in_array($value, SalesPerson::SMTP_AUTH);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'SMTP auth needs to be: ' . implode(", ", SalesPerson::SMTP_AUTH);
    }
}