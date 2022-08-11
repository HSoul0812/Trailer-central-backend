<?php

namespace App\Rules\QBO;

use Illuminate\Contracts\Validation\Rule;

class ValidStringCharacters implements Rule
{
    const PARTS_SECTION = 'parts';
    const PARTS_REGEX = "/^[-@.,!'~*_;?:()\"\/#&+\w\s]*$/";
    const MISC_PARTS_SECTION = 'misc_parts';
    const MISC_PARTS_REGEX = "/^[\w]\*$/";
    const CUSTOMERS_SECTION = 'customers';
    const CUSTOMERS_REGEX = "/^[\w]\*$/";

    /**
     * @var string
     */
    protected $section = '';

    public function __construct(string $section = self::PARTS_SECTION)
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
        if ($attribute === 'description' and !is_null($value)) {
            $value = $this->getUnEscapedString($value);
        }

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
                return self::PARTS_REGEX;
            case self::MISC_PARTS_SECTION:
            default:
                return self::MISC_PARTS_REGEX;
        };
    }

    protected function getUnEscapedString($value): string
    {
        return str_replace(
            ['\\*', '\\_'],
            ['*', '_'],
            $value
        );
    }
}
