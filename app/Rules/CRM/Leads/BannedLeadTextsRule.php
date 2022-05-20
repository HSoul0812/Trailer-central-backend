<?php

namespace App\Rules\CRM\Leads;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class BannedLeadTextsRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !$this->textIsBanned($value);
    }

    private function textIsBanned($text): bool
    {
        $stringWithoutMultipleSpaces = preg_replace('/\s+/', ' ', $text);
        return Str::contains(strtolower($stringWithoutMultipleSpaces), [
            'your website',
            'a website that your organization hosts'
        ]);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Lead contains banned text.';
    }
}
