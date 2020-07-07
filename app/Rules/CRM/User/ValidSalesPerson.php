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
        $salesPerson = SalesPerson::find($value);
        
        if ($salesPerson) {
            return true;
        }
        
        /**
         * The unassigned sales person
         */
        if (empty($value)) {
            return true;
        }
        
        
        return false;
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