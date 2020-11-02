<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Receive Facebook Catalog Payload Request
 * 
 * @author David A Conway Jr.
 */
class PayloadCatalogRequest extends Request {

    protected $rules = [
        'id' => 'required|int',
        'payload' => 'required|json'
    ];
}