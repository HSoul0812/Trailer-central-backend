<?php

namespace App\Rules\Parts;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Parts\PartOrder;

class ValidOrderStatus implements Rule
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
        return in_array($value, PartOrder::STATUS_FIELDS);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Part order status needs to be: ' . implode(", ", PartOrder::STATUS_FIELDS);                
    }
}