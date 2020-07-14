<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class GetManufacturersRequest extends Request {
    
    protected $rules = [
        'per_page' => 'integer',
        'search_term' => 'string|nullable',
    ];
    
}
