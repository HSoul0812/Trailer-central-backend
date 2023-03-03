<?php

namespace App\Rules\Marketing\Craigslist;

use App\Models\User\DealerClapp;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ValidDealer implements Rule
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

        // No Dealer ID?
        if(empty($value)) {
            return true;
        }

        // Get Valid Dealer Clapp!
        $clapp = DealerClapp::find($value);
        if(empty($clapp)) {
            return false;
        }

        // Does Clapp Belong to Dealer?!
        if($clapp->dealer_id !== $user->dealer_id) {
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
        return 'Craigslist Clapp must exist';
    }
}