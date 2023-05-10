<?php

namespace App\Http\Requests\CRM\Interactions;

use App\Http\Requests\Request;

class GetEmailDraftRequest extends Request {
    
    protected $rules = [
        'lead_id' => 'required|integer',
        'dealer_id' => 'required|integer',
        'user_id' => 'required|integer',
        'sales_person_id' => 'integer'
    ];
}