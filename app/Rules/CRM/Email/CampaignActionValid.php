<?php

namespace App\Rules\CRM\Email;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Email\Campaign;

class CampaignActionValid implements Rule
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
        // Value Exists In Campaign Status Actions?
        return in_array($value, Campaign::STATUS_ACTIONS);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Campaign action needs to be: ' . implode(", ", Campaign::STATUS_ACTIONS);                
    }
}