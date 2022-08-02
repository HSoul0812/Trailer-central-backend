<?php

namespace App\Rules\Shared;

use Illuminate\Contracts\Validation\Rule;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\EmailValidator;

/**
 * Class EmailValidation
 *
 * @package App\Rules\Shared
 */
class EmailValidation implements Rule
{
    protected const REGEX = "/^[a-zA-Z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-zA-Z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@"
        . '(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $validator = resolve(EmailValidator::class);

        return preg_match(self::REGEX, $value) && $validator->isValid($value, new RFCValidation());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be a valid email address.';
    }
}
