<?php

namespace App\Http\Requests\CRM\Interactions;

use App\Http\Requests\Request;

class GetTasksRequest extends Request {
    
    protected $rules = [
        'sort' => 'in:created_at,-created_at'
    ];
    
}
