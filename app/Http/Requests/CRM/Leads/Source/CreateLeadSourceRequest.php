<?php

namespace App\Http\Requests\CRM\Leads\Source;

use App\Http\Requests\Request;

class CreateLeadSourceRequest extends Request {
    
    protected $rules = [
        'user_id' => 'required|integer',
        'source_name' => 'required|string'
    ];
}
