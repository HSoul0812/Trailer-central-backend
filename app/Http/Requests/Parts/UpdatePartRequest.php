<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;
use App\Rules\QBO\ValidStringCharacters;

/**
 *
 * @author Eczek
 */
class UpdatePartRequest extends Request {

    protected function getRules(): array
    {
        return [
            'id' => 'required|integer',
            'vendor_id' => 'nullable|integer',
            'vehicle_specific_id' => 'nullable|integer',
            'manufacturer_id' => 'integer',
            'brand_id' => 'required|integer',
            'type_id' => 'required|integer',
            'category_id' => 'required|integer',
            'qb_id' => 'nullable|integer',
            'subcategory' => ['nullable', 'string', new ValidStringCharacters(ValidStringCharacters::PARTS_SECTION)],
            'sku' => ['required', 'string', 'parts_sku_unique:' . $this->input('id'), new ValidStringCharacters(ValidStringCharacters::PARTS_SECTION)],
            'title' => ['nullable', 'string', new ValidStringCharacters(ValidStringCharacters::PARTS_SECTION)],
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
            'description' => ['nullable'],
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
            'bins.*.quantity' => 'required|numeric',
            'is_active' => 'boolean'
        ];
    }
}
