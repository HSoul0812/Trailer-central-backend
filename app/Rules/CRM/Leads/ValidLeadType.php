<?php

namespace App\Rules\CRM\Leads;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Leads\LeadType;

/**
 * Class ValidLeadType
 * @package App\Rules\CRM\Leads
 */
class ValidLeadType implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return in_array($value, LeadType::TYPE_ARRAY_FULL);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Lead type status needs to be: ' . implode(', ', LeadType::TYPE_ARRAY_FULL);
    }
}
