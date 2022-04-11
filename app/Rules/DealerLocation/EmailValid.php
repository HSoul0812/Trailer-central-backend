<?php

namespace App\Rules\DealerLocation;

use Illuminate\Contracts\Validation\Rule;

class EmailValid implements Rule
{
    public function passes($attribute, $value)
    {
        $emails = explode(';', $value);

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return "Invalid email structure provided. Emails should be delimited with semicolon (;)";
    }
}
