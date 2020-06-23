<?php

namespace App\Rules\CRM\Leads;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Leads\Lead;

class ValidLeadStatus implements Rule
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
            case Lead::STATUS_HOT:
                break;
            case Lead::STATUS_COLD:
                break;
            case Lead::STATUS_LOST:
                break;
            case Lead::STATUS_MEDIUM:
                break;
            case Lead::STATUS_NEW_INQUIRY:
                break;
            case Lead::STATUS_UNCONTACTED:
                break;           
            case Lead::STATUS_WON:
                break;
            case Lead::STATUS_WON_CLOSED:
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
        return 'Lead status needs to be: '.
                Lead::STATUS_HOT . ', ' .
                Lead::STATUS_COLD . ',' .  
                Lead::STATUS_LOST . ',' .
                Lead::STATUS_MEDIUM . ',' .
                Lead::STATUS_NEW_INQUIRY . ',' .
                Lead::STATUS_UNCONTACTED . ',' .
                Lead::STATUS_WON . ',' .
                Lead::STATUS_WON_CLOSED . ',';            
    }
}