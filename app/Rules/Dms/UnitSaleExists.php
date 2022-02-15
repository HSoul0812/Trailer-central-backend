<?php

namespace App\Rules\Dms;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Dms\UnitSale;

class UnitSaleExists implements Rule
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
        return UnitSale::find($value);
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