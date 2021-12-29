<?php

namespace App\Rules\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ValidLead implements Rule
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
        $lead = Lead::find($value);

        if(empty($lead)) {
            return false;
        }

        if($lead->dealer_id !== $user->dealer_id && $lead->website->dealer_id !== $user->dealer_id) {
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
        return 'Sales person must exist';
    }
}
