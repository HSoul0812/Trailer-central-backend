<?php

namespace App\Http\Requests\Parts\Textrail;

class GetPartsRequest extends \App\Http\Requests\Parts\GetPartsRequest
{
    protected $rules = [
        'per_page' => 'integer',
        'sort' => 'in:price,-price,relevance,title,-title,length,-length,sku,-sku,dealer_cost,-dealer_cost,msrp,-msrp,subcategory,-subcategory,created_at,-created_at,stock,-stock',
        'type_id' => 'array',
        'type_id.*' => 'exists:textrail_types,id',
        'category_id' => 'array',
        'category_id.*' => 'exists:textrail_categories,id',
        'manufacturer_id' => 'array',
        'manufacturer_id.*' => 'exists:textrail_manufacturers,id',
        'brand_id' => 'array',
        'brand_id.*' => 'exists:textrail_brands,id',
        'price' => 'price_format',
        'dealer_id' => 'array',
        'dealer_id.*' => 'integer',
        'show_on_website' => 'boolean',
        'sku' => 'min:1|sku_type',
        'id' => 'array',
        'id.*' => 'integer',
        'is_sublet_specfic' => 'integer'
    ];
}
