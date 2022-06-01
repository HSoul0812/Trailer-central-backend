<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Get Status Marketplace Request
 * 
 * @package App\Http\Requests\Marketing\Facebook
 * @author David A Conway Jr.
 */
class StatusMarketplaceRequest extends Request {
    protected $rules = [
        'dealer_id' => 'required|int'
    ];
}