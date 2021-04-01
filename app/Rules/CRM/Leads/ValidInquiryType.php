<?php

namespace App\Rules\CRM\Leads;

use App\Services\CRM\Leads\DTOs\InquiryLead;
use Illuminate\Contracts\Validation\Rule;

class ValidInquiryType implements Rule
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
        // Inquiry Type is Valid?!
        if(!in_array($value, InquiryLead::INQUIRY_TYPES)) {
            return false;
        }

        // Return True
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Inquiry type needs to be: ' . implode(", ", InquiryLead::INQUIRY_TYPES);                
    }
}