<?php

namespace App\Rules\Inventory;

use App\Models\CRM\Dms\UnitSale;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class QuotesNotExist
 * @package App\Rules\Inventory
 */
class QuotesNotExist implements Rule
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
        return !UnitSale::query()->where('inventory_id', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Can\'t delete inventory linked to quotes. Inventory ID - :attribute';
    }
}
