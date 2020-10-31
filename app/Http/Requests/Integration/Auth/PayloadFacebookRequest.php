<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Receive Facebook Payload Request
 * 
 * @author David A Conway Jr.
 */
class PayloadFacebookRequest extends Request {

    protected $rules = [
        'id' => 'required|int',
        'payload' => 'required|json'
    ];
}