<?php

namespace App\Http\Requests\Dispatch\Facebook;

use App\Http\Requests\Request;

/**
 * Verify Facebook Marketplace Request Status
 * 
 * @package App\Http\Requests\Dispatch\Facebook
 * @author David A Conway Jr.
 */
class VerifyMarketplaceRequest extends Request {

    protected $rules = [
        'marketplace_id' => 'required|int'
    ];

}