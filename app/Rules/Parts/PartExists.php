<?php

namespace App\Rules\Parts;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Parts\Part;

class PartExists implements Rule
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
        return Part::where('id', $value)->count() > 0;
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