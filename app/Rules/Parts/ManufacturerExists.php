<?php

namespace App\Rules\Parts;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Parts\Manufacturer;

class ManufacturerExists implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (is_array($value)) {
            foreach ($value as $type) {
                if (!is_numeric($type)) {
                    return false;
                }
            }

            return Manufacturer::whereIn('id', $value)->count() === count($value);
        } else {
            if (!is_numeric($value)) {
                return false;
            }

            return Manufacturer::where('id', $value)->count() > 0;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute does not exist in the DB.';
    }
}
