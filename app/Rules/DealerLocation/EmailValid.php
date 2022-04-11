<?php

namespace App\Rules\DealerLocation;

use Illuminate\Contracts\Validation\Rule;

class EmailValid implements Rule
{
    public function passes($attribute, $value)
    {
        if (strpos($value, ',') !== false) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return "Invalid email structure provided. Emails should be delimited with semicolon (;)";
    }
}
