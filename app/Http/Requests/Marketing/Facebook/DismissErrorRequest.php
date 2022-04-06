<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Get Dismiss Marketplace Error Request
 * 
 * @package App\Http\Requests\Marketing\Facebook
 * @author David A Conway Jr.
 */
class DismissErrorRequest extends Request {
    protected $rules = [
        'id' => 'required|int',
        'error_id' => 'nullable|int'
    ];
}