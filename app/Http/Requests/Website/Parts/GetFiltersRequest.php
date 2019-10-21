<?php

namespace App\Http\Requests\Website\Parts;

use App\Http\Requests\Request;

/**
 * 
 *
 * @author Eczek
 */
class GetFiltersRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required|array',
        'dealer_id.*' => 'integer',
        'type_id' => 'array',
        'type_id.*' => 'integer',
        'manufacturer_id' => 'array',
        'manufacturer_id.*' => 'integer',
        'brand_id' => 'array',
        'brand_id.*' => 'integer',
        'category_id' => 'array',
        'category_id.*' => 'integer'
    ];
    
}
