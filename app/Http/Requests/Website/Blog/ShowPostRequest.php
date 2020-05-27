<?php

namespace App\Http\Requests\Website\Blog;

use App\Http\Requests\Request;

/**
 * 
 * 
 * @author David A Conway Jr.
 */
class ShowPostRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}
