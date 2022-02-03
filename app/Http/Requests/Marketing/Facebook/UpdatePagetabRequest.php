<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Update Facebook Page Tab Request
 * 
 * @author David A Conway Jr.
 */
class UpdatePagetabRequest extends Request {
    
    protected $rules = [
        'id' => 'required_without_all:page_id|int',
        'page_id' => 'required_without_all:id|int',
        'title' => 'nullable|string',
        'timestamp' => 'date_format:Y-m-d H:i:s'
    ];
    
}