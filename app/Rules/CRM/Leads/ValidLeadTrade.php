<?php

namespace App\Rules\CRM\Leads;

use App\Models\CRM\Leads\LeadTrade;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ValidLeadTrade
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed $parameters additional passed parameters
     * @param  Validator
     * @return bool
     */
    public function passes(string $attribute, string $value, array $parameters, $validator): bool
    {
        $otherField = $parameters[0];
        $leadId = data_get($validator->getData(), $otherField);

        $user = Auth::user();
        $trade = LeadTrade::find($value);

        if(empty($trade)) {
            return false;
        }

        if($trade->lead->dealer_id !== $user->dealer_id
            || $trade->lead->getKey() !== $leadId) {
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
        return 'Lead Trade must exist';
    }
}
