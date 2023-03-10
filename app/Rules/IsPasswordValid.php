<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsPasswordValid implements Rule
{
    /**
     * Determine if the Uppercase Validation Rule passes.
     *
     * @var boolean
     */
    public $uppercasePasses = true;

    /**
     * Determine if the Lowercase Validation Rule passes.
     *
     * @var boolean
     */
    public $lowercasePasses = true;

    /**
     * Determine if the Numeric Validation Rule passes.
     *
     * @var boolean
     */
    public $numericPasses = true;

    /**
     * Determine if the Special Character Validation Rule passes.
     *
     * @var boolean
     */
    public $specialCharacterPasses = true;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->uppercasePasses = preg_match('/[^A-Z]/', $value);
        $this->lowercasePasses = preg_match('/[^a-z]/', $value);
        $this->numericPasses = preg_match('/[^0-9]/', $value);
        $this->specialCharacterPasses = preg_match('/[^@$!%*#?&_]/', $value);

        return ($this->uppercasePasses && $this->lowercasePasses && $this->numericPasses && $this->specialCharacterPasses);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (!$this->uppercasePasses) {
            return 'The :attribute must contain at least one uppercase character.';
        } else if (!$this->lowercasePasses) {
            return 'The :attribute must contain at least one lowercase character.';
        } else if (!$this->numericPasses) {
            return 'The :attribute must contain at least one number.';
        } else if (!$this->specialCharacterPasses) {
            return 'The :attribute must contain at least one special character.';
        }
    }
}
