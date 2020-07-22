<?php

namespace App\Rules\CRM\Interactions;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Interactions\Interaction;

class ValidInteractionType implements Rule
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
        return in_array($value, Interaction::INTERACTION_TYPES);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Interaction type needs to be: ' . implode(", ", Interaction::INTERACTION_TYPES);                
    }
}