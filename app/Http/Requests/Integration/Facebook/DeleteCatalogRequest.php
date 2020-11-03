<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Delete Catalog Request
 *
 * @author David A Conway Jr.
 */
class DeleteBlastRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}
