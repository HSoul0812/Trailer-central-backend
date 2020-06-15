<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

class GetLeadsRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'integer'
    ];
    
}
