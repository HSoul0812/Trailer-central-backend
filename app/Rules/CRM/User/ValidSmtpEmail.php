<?php

namespace App\Rules\CRM\User;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\User\SalesPerson;
use Illuminate\Support\Facades\Auth;

class ValidSmtpEmail implements Rule
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

        // Get Sales Person With SMTP Email
        $salesPerson = SalesPerson::where('user_id', $user->newDealerUser->user_id)
                                  ->where('smtp_email', $value)->first();
        if(empty($salesPerson)) {
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