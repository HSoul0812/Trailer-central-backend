<?php

namespace App\Rules\Dms\Quickbooks;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Dms\Quickbooks\Account;

class AccountNameUnique implements Rule
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
        return Account::find($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute does not exist in the DB.';
    }
}