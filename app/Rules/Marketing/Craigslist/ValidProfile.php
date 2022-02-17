<?php

namespace App\Rules\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Profile;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ValidProfile implements Rule
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

        // No Profile?
        if(empty($value)) {
            return true;
        }

        // Get Valid Dealer Profile!
        $profile = Profile::find($value);
        if(empty($profile)) {
            return false;
        }

        // Does CL Profile Belong to Dealer?!
        if($profile->dealer_id !== $user->dealer_id) {
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
        return 'Craigslist Profile must exist';
    }
}