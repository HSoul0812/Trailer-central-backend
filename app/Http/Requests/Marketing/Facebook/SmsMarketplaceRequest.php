<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Get Facebook SMS Number Marketplace Request
 * 
 * @package App\Http\Requests\Marketing\Facebook
 * @author David A Conway Jr.
 */
class SmsMarketplaceRequest extends Request {
    protected $rules = [
        'sms_number' => 'required|string'
    ];
}