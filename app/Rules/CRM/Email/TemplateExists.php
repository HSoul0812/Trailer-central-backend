<?php

namespace App\Rules\CRM\Email;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Email\Template;

class TemplateExists implements Rule
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
        return Template::where('id', $value)->count() > 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute does not exist in the DB.';
    }
}