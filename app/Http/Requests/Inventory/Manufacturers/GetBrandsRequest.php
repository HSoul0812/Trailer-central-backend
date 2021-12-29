<?php

namespace App\Http\Requests\Inventory\Manufacturers;

use App\Http\Requests\Request;

class GetInventoryRequest extends Request 
{
    protected $rules = [
        'per_page' => 'integer',
        'search_term' => 'string',
    ];
}
