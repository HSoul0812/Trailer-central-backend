<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;


class PriceFormat implements Rule
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
        $explodedPrice = explode('TO', str_replace(']', '', str_replace('[', '', $value)));
        
        foreach($explodedPrice as $index => $price) {
            $explodedPrice[$index] = (double)trim($price);
        }        
        
        if (!isset($explodedPrice[0])) {
            return false;
        }        
        
        if (!is_numeric($explodedPrice[0]) || (isset($explodedPrice[1]) && !is_numeric($explodedPrice[1])) ) {
            return false;
        }
        
        if (isset($explodedPrice[1]) && ($explodedPrice[1] <= $explodedPrice[0])) {
            return false;
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
        return 'The :attribute does not exist in the DB.';
    }
}