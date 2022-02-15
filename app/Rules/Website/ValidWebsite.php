<?php

namespace App\Rules\Website;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Website\Website;
use Illuminate\Support\Facades\Auth;

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

        // Does Inventory Belong to Dealer?!
        if($website->dealer_id !== $user->dealer_id) {
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
        return 'Website must exist and belong to authenticated dealer!';
    }
}
