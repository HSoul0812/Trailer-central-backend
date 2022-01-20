<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Assign Chat Request
 */
class AssignChatRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer',
        'sales_person_ids' => 'required|array',
        'sales_person_ids.*' => 'required|integer|sales_person_valid'
    ];
    
}
