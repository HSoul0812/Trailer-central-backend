<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

/**
 * Class BulkUpdateInventoryRequest
 * @package App\Http\Requests\Inventory
 */
class MassUpdateInventoryRequest extends Request
{
    protected $rules = [
        '*' => 'allowed_attributes:dealer_id,show_on_rvt,show_on_auction123',
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'show_on_rvt' => 'boolean',
        'show_on_auction123' => 'boolean',
    ];
}
