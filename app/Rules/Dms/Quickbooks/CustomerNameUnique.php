<?php

namespace App\Rules\Dms\Quickbooks;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\User\Customer;

class CustomerNameUnique implements Rule
{
    public function validate($attribute, $value, $parameters) {   
        if (!empty($parameters)) {
            $dealerId = current($parameters);            
            // Check if there are any customers with same display_name
            $duplicatedCustomers = Customer::where([
                'display_name' => $value,
                'dealer_id' => $dealerId,
            ])->whereNull("deleted_at")->get();

            if (count($duplicatedCustomers)) {
                return false;
            }
            return true;
        }
        
        return false;        
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'There are customer with same display name in the DB.';
    }
}