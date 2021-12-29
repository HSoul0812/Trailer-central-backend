<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

/**
 * Class SaveInventoryAttributeRequest
 *
 * @package App\Http\Requests\Inventory
 */
class SaveInventoryAttributeRequest extends Request
{
    protected $rules = [
        'inventory_id' => [
            'integer',
            'required',
            'exists:inventory,inventory_id',
        ],
        'attributes' => [
            'array',
            'min:1',
        ],
        'attributes.*.id' => [
            'integer',
            'required',
            'exists:eav_attribute,attribute_id',
        ],
        'attributes.*.value' => [
            'string',
            'required',
            'min:1',
        ],
        'dealer_id' => [
            'integer',
            'exists:dealer,dealer_id',
        ],
    ];
}
