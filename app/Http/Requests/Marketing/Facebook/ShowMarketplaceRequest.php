<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Show Facebook Marketplace Request
 * 
 * @package App\Http\Requests\Marketing\Facebook
 * @author David A Conway Jr.
 */
class ShowMarketplaceRequest extends Request {

    protected $rules = [
        'id' => 'required|integer'
    ];

}