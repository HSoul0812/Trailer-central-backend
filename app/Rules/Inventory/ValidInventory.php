<?php

namespace App\Rules\CRM\User;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Inventory\Inventory;

class ValidInventory implements Rule
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

        // Get Valid Inventory!
        $inventory = Inventory::find($value);
        if(empty($inventory)) {
            return false;
        }

        // Does Inventory Belong to Dealer?!
        if($inventory->dealer_id !== $user->dealer_id) {
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
        return 'Inventory must exist';
    }
}