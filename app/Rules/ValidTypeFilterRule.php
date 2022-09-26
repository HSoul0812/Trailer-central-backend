<?php

namespace App\Rules;

use App\Models\Parts\Type;
use Illuminate\Contracts\Validation\Rule;

class ValidTypeFilterRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($_attribute, $value)
    {
        // Iterate over the array and sequentially check if the type passed exists in the DB
        foreach($value as $type) {
            if(Type::where('name', $type)->count() === 0) {
                return false;
            }

            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid :attribute passed to endpoint';
    }
}
