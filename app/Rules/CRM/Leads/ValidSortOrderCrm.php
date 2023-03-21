<?php

namespace App\Rules\CRM\Leads;

use Illuminate\Contracts\Validation\Rule;
use App\Repositories\CRM\Leads\LeadRepository;

class ValidSortOrderCrm implements Rule
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
        return (int) $value < count(LeadRepository::SORT_ORDERS_CRM);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid Sort Order CRM';
    }
}