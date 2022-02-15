<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class GetManufacturersRequest extends Request {
    
    protected $rules = [
        'per_page' => 'integer',
        'search_term' => 'string|nullable',
        'ids' => 'array',
        'ids.*' => 'inventory_mfg_id_valid',
        'name' => 'array',
        'name.*' => 'inventory_mfg_name_valid',
    ];
    
}
