<?php

namespace App\Rules\CRM\Interactions;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Interactions\Interaction;

class ValidInteractionNote
{
    public function passes(string $attribute, $value, array $parameters, $validator): bool
    {
        $interactionType = data_get($validator->getData(), $parameters[0]);

        // only not required if interaction type is contact
        if (strtoupper($interactionType) === Interaction::TYPE_CONTACT)
            return true;
        
        return !empty($value) && is_string($value);
    }
}