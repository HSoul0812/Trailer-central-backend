<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class InventoryBulkUpdateManufacturerRequest extends Request
{
    protected $rules = [
        'from_manufacturer' => 'string|required',
        'to_manufacturer' => 'string|required'
    ];
}
