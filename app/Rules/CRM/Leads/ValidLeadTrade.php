<?php

namespace App\Rules\CRM\Leads;

use App\Models\CRM\Leads\LeadTrade;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ValidLeadTrade implements Rule
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
        $user = Auth::user();
        $trade = LeadTrade::find($value);

        if(empty($trade)) {
            return false;
        }

        if($trade->lead->dealer_id !== $user->dealer_id) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Lead Trade must exist';
    }
}
