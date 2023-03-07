<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

/**
 * Class GetInventoryItemRequest
 * @package App\Http\Requests\Inventory
 */
class GetInventoryItemRequest extends Request
{
    protected $rules = [
        'id' => 'required|integer|exists:App\Models\Inventory\Inventory,inventory_id',
        'include' => 'string|valid_include:website,repairOrders,attributes,features,clapps,activeListings,paymentCalculator'
    ];
}
