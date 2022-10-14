<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Validation\Rule;

/**
 * Class CreateInventoryRequest
 * @package App\Http\Requests\Inventory
 */
class CreateInventoryRequest extends SaveInventoryRequest
{
    protected function getRules(): array
    {
        return array_merge(parent::getRules(), [
            'stock' => [
                'string',
                'max:50', Rule::unique('inventory', 'stock')->where('dealer_id', $this->dealer_id)]
        ]);
    }
}
