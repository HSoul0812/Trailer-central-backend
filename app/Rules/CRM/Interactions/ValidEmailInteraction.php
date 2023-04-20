<?php

namespace App\Rules\CRM\Interactions;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Interactions\Interaction;

class ValidEmailInteraction implements Rule
{
    public function passes($attribute, $value)
    {
        return Interaction::where([
            'interaction_id' => $value,
            'interaction_type' => Interaction::TYPE_EMAIL
        ])->count() > 0;
    }

    public function message()
    {
        return 'Email Interaction does not exist';
    }
}