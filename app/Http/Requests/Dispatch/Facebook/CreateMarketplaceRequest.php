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
        'id' => 'required|int',
        'inventory_id' => 'required|integer|min:1|required|exists:inventory,inventory_id',
        'facebook_id' => 'required|int',
        'account_type' => 'required|in:page,user',
        'page_id' => 'nullable|int',
        'listing_type' => 'required|in:item,vehicle,home,job',
        'specific_type' => 'required|in:car,motorcycle,powersport,rv,trailer,boat,commercial,other',
        'year' => 'required|int',
        'price' => 'required|numeric',
        'make' => 'required|string',
        'model' => 'required|string',
        'description' => 'required|string',
        'location' => 'required|string',
        'color_exterior' => 'required|string',
        'color_interior' => 'required|string',
        'trim' => 'nullable|string',
        'mileage' => 'nullable|int',
        'body_style' => 'nullable|string',
        'condition' => 'nullable|string',
        'transmission' => 'nullable|string',
        'fuel_type' => 'nullable|string',
        'status' => 'required|in:active,deleted,expired',
        'images' => 'required|array',
        'images.*' => 'required|int'
    ];

}