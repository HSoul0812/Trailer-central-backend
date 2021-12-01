<?php

namespace App\Http\Requests\Dispatch\Facebook;

use App\Http\Requests\Request;

/**
 * Single Facebook Marketplace Request Status
 * 
 * @package App\Http\Requests\Dispatch\Facebook
 * @author David A Conway Jr.
 */
class ShowMarketplaceRequest extends Request {

    protected $rules = [
        'id' => 'required|int'
    ];

}