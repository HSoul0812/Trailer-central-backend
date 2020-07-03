<?php

namespace App\Rules\CRM\Leads;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Leads\LeadType;

class ValidLeadType implements Rule
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
        switch ($value) {
            case LeadType::TYPE_BUILD:
                break;
            case LeadType::TYPE_CALL:
                break;
            case LeadType::TYPE_GENERAL:
                break;
            case LeadType::TYPE_CRAIGSLIST:
                break;
            case LeadType::TYPE_INVENTORY:
                break;
            case LeadType::TYPE_TEXT:
                break;           
            case LeadType::TYPE_SHOWROOM_MODEL:
                break;
            case LeadType::TYPE_JOTFORM:
                break;
            case LeadType::TYPE_RENTALS:
                break;
            case LeadType::TYPE_FINANCING:
                break;
            case LeadType::TYPE_SERVICE:
                break;     
            default:
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
        return 'Lead type status needs to be: '.
                LeadType::TYPE_BUILD . ', ' .
                LeadType::TYPE_CALL . ',' .  
                LeadType::TYPE_GENERAL . ',' .
                LeadType::TYPE_CRAIGSLIST . ',' .
                LeadType::TYPE_INVENTORY . ',' .
                LeadType::TYPE_TEXT . ',' .
                LeadType::TYPE_SHOWROOM_MODEL . ',' .
                LeadType::TYPE_JOTFORM . ',' .
                LeadType::TYPE_RENTALS . ',' .
                LeadType::TYPE_FINANCING . ',' .
                LeadType::TYPE_SERVICE;                
    }
}