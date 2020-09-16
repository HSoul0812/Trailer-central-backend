<?php

namespace App\Rules\Inventory;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Inventory\Inventory;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventory\Floorplan\Vendor;

class VendorExists implements Rule
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
        $vendor = Vendor::find($value);
        
        if ($vendor) {
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
        return 'Vendor must exist';
    }
}