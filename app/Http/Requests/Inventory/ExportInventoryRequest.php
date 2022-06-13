<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class ExportInventoryRequest extends Request
{
    protected $rules = [
        'inventory_id' => 'required|inventory_valid',
        'format' => 'required|in:pdf'
    ];
}
