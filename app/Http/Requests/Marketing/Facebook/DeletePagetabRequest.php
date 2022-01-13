<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Delete Facebook Page Tab Request
 *
 * @author David A Conway Jr.
 */
class DeletePagetabRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}
