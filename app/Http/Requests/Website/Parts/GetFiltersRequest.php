<?php

namespace App\Http\Requests\Website\Parts;

use App\Http\Requests\Request;

/**
 *
 *
 * @author Eczek
 */
class GetFiltersRequest extends Request 
{
    /**
     * Get validation rules for a request
     */
    protected $rules = [
        'dealer_id' => 'required|array',
        'dealer_id.*' => 'integer',
        'type_id' => 'array',
        'type_id.*' => 'exists:part_types,name',
        'manufacturer_id' => 'array',
        'brand_id' => 'array',
        'category_id' => 'array',
        'subcategory' => 'array',
    ];
}
