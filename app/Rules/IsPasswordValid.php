<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

/**
 * Class IsPasswordValid
 *
 * @package App\Rules;
 */
class IsPasswordValid implements Rule
{
    /**
     * Minimum password length
     *
     * @var int
     */
    const MIN_LENGTH = 8;

    /**
     * Mixed case pattern
     *
     * @var string
     */
    const MIXED_CASE_PATTERN = '/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u';

    /**
     * Letter case pattern
     *
     * @var string
     */
    const LETTER_CASE_PATTERN = '/\pL/u';

    /**
     * Symbol case pattern
     *
     * @var string
     */
    const SYMBOL_CASE_PATTERN = '/\p{Z}|\p{S}|\p{P}/u';

    /**
     * Number case pattern
     *
     * @var string
     */
    const NUMBER_CASE_PATTERN = '/\pN/u';

    /**
     * Determine if the mixed case validation rule passes
     *
     * @var boolean
     */
    public $mixedCase = true;

    /**
     * Determine if the letters validation rule passes
     *
     * @var boolean
     */
    public $letters = true;

    /**
     * Determine if the numeric validation rule passes
     *
     * @var boolean
     */
    public $numbers = true;

    /**
     * Determine if the special character validation rule passes
     *
     * @var boolean
     */
    public $symbols = true;

    /**
     * Determine if the validation rule passes
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->mixedCase = preg_match(self::MIXED_CASE_PATTERN, $value);
        $this->letters = preg_match(self::LETTER_CASE_PATTERN, $value);
        $this->symbols = preg_match(self::SYMBOL_CASE_PATTERN, $value);
        $this->numbers = preg_match(self::NUMBER_CASE_PATTERN, $value);

        return Str::length($value) >= self::MIN_LENGTH
            && $this->mixedCase
            && $this->letters
            && $this->numbers
            && $this->symbols;
    }

    /**
     * Get the validation error message
     *
     * @return string
     */
    public function message(): string
    {
        $message = 'The :attribute ' . self::lengthMessage();

        if (!$this->mixedCase) {
            $message .= ' and contain at least one uppercase and lowercase character.';
        } elseif (!$this->numbers) {
            $message .= ' and contain at least one number.';
        } elseif (!$this->letters) {
            $message .= ' and contain at least one letter.';
        } elseif (!$this->symbols) {
            $message .= ' and contain at least one special character.';
        }

        return $message;
    }

    /**
     * @return string
     */
    private static function lengthMessage(): string
    {
        return 'must be at least ' . self::MIN_LENGTH . ' characters';
    }
}
