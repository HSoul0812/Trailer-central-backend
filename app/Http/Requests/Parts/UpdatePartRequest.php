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
        'subcategory' => 'nullable|string',
        'title' => 'nullable|string',
        'alternative_part_number' => 'nullable|string',
        'price' => 'numeric',
        'dealer_cost' => 'nullable|numeric',
        'latest_cost' => 'nullable|numeric',
        'msrp' => 'nullable|numeric',
        'shipping_fee' => 'nullable|numeric',
        'use_handling_fee' => 'nullable|boolean',
        'handling_fee' => 'nullable|numeric',
        'fulfillment_type' => 'nullable|numeric',
        'weight' => 'nullable|numeric',
        'weight_rating' => 'string|nullable',
        'description' => 'nullable',
        'qty' => 'nullable|integer',
        'show_on_website' => 'boolean',
        'is_vehicle_specific' => 'boolean',
        'vehicle_make' => 'string|nullable',
        'vehicle_model' => 'string',
        'vehicle_year_from' => 'integer',
        'vehicle_year_to' => 'integer',
        'stock_max' => 'integer|nullable',
        'stock_min' => 'integer|nullable',
        'images' => 'array',
        'images.*.url' => 'url',
        'images.*.position' => 'integer',
        'bins' => 'array',
        'bins.*.bin_id' => 'required|integer',
        'bins.*.quantity' => 'required|numeric'
    ];

    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->rules['sku'] = 'required|string|parts_sku_unique:'.$this->input('id');
    }
}
