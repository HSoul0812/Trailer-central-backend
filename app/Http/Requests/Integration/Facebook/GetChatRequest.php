<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Get Facebook Chat Request
 * 
 * @author David A Conway Jr.
 */
class GetChatRequest extends Request {
    
    protected $rules = [
        'user_id' => 'required|integer',
        'sales_person_id' => 'integer',
        'per_page' => 'integer',
        'sort' => 'in:created_at,-created_at,updated_at,-updated_at',
        'id' => 'array',
        'id.*' => 'integer'
    ];
}
