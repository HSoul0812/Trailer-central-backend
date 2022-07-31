<?php

namespace App\Rules\QBO;

use Illuminate\Contracts\Validation\Rule;

class ValidStringCharacters implements Rule
{
    const PARTS_SECTION = 'parts';
    const MISC_PARTS_SECTION = 'misc_parts';
    const CUSTOMERS_SECTION = 'customers';

    /**
     * @var string
     */
    protected $section = '';

    public function __construct(string $section = 'parts')
    {
        $this->section = $section;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match($this->getValidStringRegex(), $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute has invalid special characters.';
    }

    protected function getValidStringRegex(): string
    {
        switch ($this->section) {
            case self::PARTS_SECTION:
                return "/^[-@.,!'~*_;?:()\"\/#&+\w\s]\*$/";
            case self::MISC_PARTS_SECTION:
                return "/^[\w]\*$/";
            default:
                return "/^[\w]\*$/";
        };
    }
}
