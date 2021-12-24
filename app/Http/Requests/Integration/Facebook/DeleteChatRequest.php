<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Delete Chat Request
 *
 * @author David A Conway Jr.
 */
class DeleteChatRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}
