<?php

namespace App\Rules\Inventory;

use App\Models\Inventory\Inventory;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class UniqueStock
 * @package App\Rules\Inventory
 */
class UniqueStock implements Rule
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
        $dealerId = app('request')->get('dealer_id');

        $params = [
            ['stock', '=', $value],
            ['dealer_id', '=', $dealerId]
        ];

        $inventoryId = app('request')->get('inventory_id');
        if(!empty($inventoryId)) {
            $params[] = ['inventory_id', '<>', $inventoryId];
        }

        return Inventory::where($params)->count() === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute exist in the DB.';
    }
}
