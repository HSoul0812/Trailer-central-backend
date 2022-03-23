<?php

namespace App\Http\Requests\Dispatch\Facebook;

use App\Http\Requests\Request;

/**
 * Create Facebook Marketplace Inventory Item
 * 
 * @package App\Http\Requests\Dispatch\Facebook
 * @author David A Conway Jr.
 */
class CreateMarketplaceRequest extends Request {

    protected $rules = [
        'marketplace_id' => 'required|int',
        'inventory_id' => 'required|integer|min:1|required|exists:inventory,inventory_id',
        'facebook_id' => 'required|int',
        'status' => 'required|in:active,invalid,failed,deleted,expired',
        'account_type' => 'in:page,user',
        'page_id' => 'nullable|int',
        'username' => 'required|string',
        'listing_type' => 'in:item,vehicle,home,job',
        'specific_type' => 'in:car,motorcycle,powersport,rv,trailer,boat,commercial,other',
        'year' => 'int',
        'price' => 'numeric',
        'make' => 'string',
        'model' => 'string',
        'description' => 'string',
        'location' => 'string',
        'color_exterior' => 'string',
        'color_interior' => 'string',
        'trim' => 'nullable|string',
        'mileage' => 'nullable|int',
        'body_style' => 'nullable|string',
        'condition' => 'nullable|string',
        'transmission' => 'nullable|string',
        'fuel_type' => 'nullable|string',
        'images' => 'array',
        'images.*' => 'int'
    ];

}