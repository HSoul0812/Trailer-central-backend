<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

/**
 * Class ExistsInventoryRequest
 * @package App\Http\Requests\Inventory
 */
class ExistsInventoryRequest extends Request
{
    protected $rules = [
        'stock' => 'string',
        'dealer_id' => 'required|exists:App\Models\User\User,dealer_id'
    ];
}
