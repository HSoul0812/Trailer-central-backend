<?php

namespace App\Http\Requests\CRM\Interactions;

use App\Http\Requests\Request;

class GetReplyEmailDraftRequest extends Request {
    
    protected $rules = [
        'lead_id' => 'required|integer',
        'interaction_id' => 'required|integer|valid_email_interaction',
        'dealer_id' => 'required|integer',
        'user_id' => 'required|integer',
        'sales_person_id' => 'integer'
    ];
}