<?php

namespace App\Http\Requests\CRM\Interactions;

use App\Http\Requests\Request;

class GetContactDateRequest extends Request {
    
    protected $rules = [

        'lead_id' => 'required|integer'
    ];
    
}
