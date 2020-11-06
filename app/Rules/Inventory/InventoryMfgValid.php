<?php

namespace App\Rules\Inventory;

use App\Models\Inventory\InventoryMfg;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class InventoryMfgValid
 * @package App\Rules\Inventory
 */
class InventoryMfgValid implements Rule
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
        return InventoryMfg::where('name', $value)->count() > 0;
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
