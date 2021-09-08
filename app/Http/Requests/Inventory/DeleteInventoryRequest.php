<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Models\CRM\Dms\UnitSale;

/**
 * Class DeleteInventoryRequest
 * @package App\Http\Requests\Inventory
 */
class DeleteInventoryRequest extends Request
{
    protected $rules = [
        'id' => 'required|integer|inventory_valid|inventory_quotes_not_exist'
    ];
}
