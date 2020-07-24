<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

/**
 * Class DeleteInventoryRequest
 * @package App\Http\Requests\Inventory
 */
class DeleteInventoryRequest extends Request
{
    protected $rules = [
        'id' => 'required|integer'
    ];
}
