<?php

namespace App\Rules\Parts;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class SkuType
 * @package App\Rules\Parts
 */
class SkuType implements Rule
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
        $operators = ['contain', 'dncontain'];

        foreach ($operators as $operator) {
            if (isset($value[$operator])) {
                if (!is_array($value[$operator])) {
                    return false;
                }

                foreach ($value[$operator] as $item) {
                    if (!is_string($item)) {
                        return false;
                    }
                }
            }
        }

        if (!isset($value[$operators[0]]) && !isset($value[$operators[1]])) {
            return is_string($value);
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute needs to be an string or string[]';
    }
}
