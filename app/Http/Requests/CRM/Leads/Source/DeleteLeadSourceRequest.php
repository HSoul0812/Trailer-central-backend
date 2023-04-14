<?php

namespace App\Http\Requests\CRM\Leads\Source;

use App\Http\Requests\Request;

class DeleteLeadSourceRequest extends Request {
    
    protected $rules = [
        'user_id' => 'required|integer',
        'id' => 'required|integer'
    ];
}
