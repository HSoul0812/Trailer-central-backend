<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Show Facebook Catalog Request
 * 
 * @author David A Conway Jr.
 */
class ShowCatalogRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];

}
