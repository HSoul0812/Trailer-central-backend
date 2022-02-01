<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

/**
 * Class GetAllInventoryTitlesRequest
 *
 * @package App\Http\Requests\Inventory
 */
class GetAllInventoryTitlesRequest extends Request
{
    protected $rules = [
        'dealer_id' => [
            'integer',
            'min:1',
            'exists:dealer,dealer_id',
        ],
        'customer_id' => [
            'integer',
            'min:1',
            'exists:dms_customer,id',
        ],
    ];
}
