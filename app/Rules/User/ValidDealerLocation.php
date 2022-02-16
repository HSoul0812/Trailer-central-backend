<?php

namespace App\Rules\User;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User\DealerLocation;
use Illuminate\Support\Facades\Auth;

class ValidDealerLocation implements Rule
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

        // No Dealer Location?
        if(empty($value)) {
            return true;
        }

        // Get Valid Dealer Location!
        $dealerLocation = DealerLocation::withTrashed()->find($value);
        if(empty($dealerLocation)) {
            return false;
        }

        // Does Dealer Location Belong to Dealer?!
        if($dealerLocation->dealer_id !== $user->dealer_id) {
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
        return 'Dealer Location must exist';
    }
}