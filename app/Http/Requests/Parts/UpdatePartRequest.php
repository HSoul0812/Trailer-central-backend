<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 *  
 * @author Eczek
 */
class UpdatePartRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer',
        'vendor_id' => 'nullable|integer',
        'vehicle_specific_id' => 'nullable|integer',
        'manufacturer_id' => 'integer',
        'brand_id' => 'required|integer',
        'type_id' => 'required|integer',
        'category_id' => 'required|integer',
        'qb_id' => 'nullable|integer',
        'subcategory' => 'required|string',
        'title' => 'nullable|string',
        'sku' => 'required|string',
        'price' => 'numeric',
        'dealer_cost' => 'nullable|numeric',
        'msrp' => 'nullable|numeric',
        'weight' => 'nullable|numeric',
        'weight_rating' => 'string',
        'description' => 'nullable',
        'qty' => 'nullable|integer',
        'show_on_website' => 'boolean',
        'is_vehicle_specific' => 'boolean',
        'vehicle_make' => 'string',
        'vehicle_model' => 'string',
        'vehicle_year_from' => 'integer',
        'vehicle_year_to' => 'integer',
        'images' => 'array',
        'images.*.url' => 'url',
        'images.*.position' => 'integer'
    ];
    
}
