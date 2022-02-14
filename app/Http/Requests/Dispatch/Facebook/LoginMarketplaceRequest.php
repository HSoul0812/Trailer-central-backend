<?php

namespace App\Http\Requests\Dispatch\Facebook;

use App\Http\Requests\Request;

/**
 * Login Facebook Marketplace Request
 * 
 * @package App\Http\Requests\Dispatch\Facebook
 * @author David A Conway Jr.
 */
class LoginMarketplaceRequest extends Request {

    protected $rules = [
        'ip_address' => 'required|ip',
        'client_uuid' => [
            'required',
            'regex:/fbm\d{10,}/'
        ],
        'version' => [
            'required',
            'regex:/\d+[.]\d+[.]\d+/'
        ]
    ];

}