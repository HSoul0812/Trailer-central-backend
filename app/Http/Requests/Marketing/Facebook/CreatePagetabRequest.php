<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Create Facebook Page Tab Request
 * 
 * @author David A Conway Jr.
 */
class CreatePagetabRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer',
        'page_id' => 'required|int',
        'title' => 'required|string',
        'timestamp' => 'date_format:Y-m-d H:i:s'
    ];
}