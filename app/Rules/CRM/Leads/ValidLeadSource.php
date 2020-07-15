<?php

namespace App\Rules\CRM\Leads;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Leads\LeadSource;
use Illuminate\Support\Facades\Auth;

class ValidLeadSource implements Rule
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
        
        if (empty($user)) {
            return false;
        }
        
        $leadSource = LeadSource::where('source_name', $value)
                        ->where('deleted', 0)
                        ->where('user_id', $user->dealer_id)
                        ->first();
        
        if (empty($leadSource)) {
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
        return 'Invalid Lead Source';         
    }
}