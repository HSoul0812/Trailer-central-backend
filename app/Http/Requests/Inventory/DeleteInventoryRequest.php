<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class DeleteInventoryRequest extends Request
{
    protected array $rules = [
        'inventory_id' => 'required|integer',
    ];
}
