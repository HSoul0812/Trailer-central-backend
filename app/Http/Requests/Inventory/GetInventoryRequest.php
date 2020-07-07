<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class GetInventoryRequest extends Request {
    
    protected $rules = [
        'per_page' => 'integer',
        'sort' => 'in:title,-title',
        'search_term' => 'string',
        'dealer_id' => 'array',
        'dealer_id.*' => 'integer'
    ];
    
}
