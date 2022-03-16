<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Get TFA Marketplace Request
 * 
 * @package App\Http\Requests\Marketing\Facebook
 * @author David A Conway Jr.
 */
class TfaMarketplaceRequest extends Request {
    protected $rules = [
        'dealer_id' => 'required|int'
    ];
}