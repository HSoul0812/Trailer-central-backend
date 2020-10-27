<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * Show Part Order Request
 *
 * @author David A Conway Jr.
 */
class ShowPartOrderRequest extends Request {

    protected $rules = [
        'id' => 'required|integer'
    ];
}
