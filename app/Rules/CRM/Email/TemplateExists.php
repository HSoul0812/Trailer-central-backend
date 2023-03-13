<?php

namespace App\Rules\CRM\Email;

use App\Models\CRM\Email\Template;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class TemplateExists
 * @package App\Rules\CRM\Email
 */
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
        $userId = request()->input('user_id');

        return Template::query()->where('id', $value)->where('user_id', $userId)->exists();
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
