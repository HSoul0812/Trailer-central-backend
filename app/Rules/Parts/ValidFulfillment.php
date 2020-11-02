<?php

namespace App\Rules\Parts;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Parts\PartOrder;

class ValidFulfillment implements Rule
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
        // Value Exists In Interaction Types?
        return in_array($value, PartOrder::FULFILLMENT_TYPES);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Fulfillment type needs to be: ' . implode(", ", PartOrder::FULFILLMENT_TYPES);                
    }
}