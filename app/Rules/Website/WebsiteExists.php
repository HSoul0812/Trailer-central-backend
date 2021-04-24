<?php

namespace App\Rules\Website;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Website\Website;

class WebsiteExists implements Rule
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
        // Get Valid Website!
        return !empty(Website::find($value));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Website must exist';
    }
}
