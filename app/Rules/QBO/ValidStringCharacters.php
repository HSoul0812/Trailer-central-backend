<?php

namespace App\Rules\QBO;

use Illuminate\Contracts\Validation\Rule;

class ValidStringCharacters implements Rule
{
    // Parts
    const PARTS_SECTION = 'parts';
    const PARTS_REGEX = "/^[-@.,!'~*_;?:()\"\/#&+\w\s]*$/";

    // Parts
    const CUSTOMERS_SECTION = 'customers';
    const CUSTOMERS_REGEX = "/^[-@.,!'~*_;?#&+\w\s]*$/";
    const CUSTOMERS_POSTAL_CODE_SECTION = 'customer_postal_code';
    const CUSTOMERS_POSTAL_CODE_REGEX = "/^[\d\w-]+$/";
    const CUSTOMERS_REGION_SECTION = 'customer_region';
    const CUSTOMERS_REGION_REGEX = "/^[A-Za-z ]+$/";
    const CUSTOMERS_PHONE_SECTION = 'customer_phone';
    const CUSTOMERS_PHONE_REGEX = "/^[(\+\d{1,2})?\d\s()-]+$/";

    const MISC_PARTS_SECTION = 'misc_parts';
    const MISC_PARTS_REGEX = "/^[\w]\*$/";

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
            case self::CUSTOMERS_SECTION:
                return self::CUSTOMERS_REGEX;
            case self::CUSTOMERS_POSTAL_CODE_SECTION:
                return self::CUSTOMERS_POSTAL_CODE_REGEX;
            case self::CUSTOMERS_REGION_SECTION:
                return self::CUSTOMERS_REGION_REGEX;
            case self::CUSTOMERS_PHONE_SECTION:
                return self::CUSTOMERS_PHONE_REGEX;
            case self::MISC_PARTS_SECTION:
            default:
                return self::MISC_PARTS_REGEX;
        }
    }
}
