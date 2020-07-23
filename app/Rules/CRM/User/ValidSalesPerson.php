<?php

namespace App\Rules\CRM\User;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\User\SalesPerson;

class ValidSalesPerson implements Rule
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

        // No Sales Person?
        if(empty($value)) {
            return true;
        }

        // Get Valid Sales Person!
        $salesPerson = SalesPerson::find($value);
        if(empty($salesPerson)) {
            return false;
        }

        // Does Sales Person Belong to Dealer?!
        if($salesPerson->user_id !== $user->newDealerUser->user_id) {
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
        return 'Sales person must exist';
    }
}