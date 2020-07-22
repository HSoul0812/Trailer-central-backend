<?php

namespace App\Rules\CRM\User;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Website\Website;

class ValidWebsite implements Rule
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
        // Must Be Authorized!
        $user = Auth::user();
        if (empty($user)) {
            return false;
        }

        // Get Valid Website!
        $website = Website::find($value);
        if(empty($website)) {
            return false;
        }

        // Success!
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Website must exist';
    }
}