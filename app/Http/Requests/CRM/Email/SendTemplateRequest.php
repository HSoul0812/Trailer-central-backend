<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Send Template Request
 * 
 * @author David A Conway Jr.
 */
class SendTemplateRequest extends Request {

    protected $rules = [
        'user_id' => 'required|integer',
        'sales_person_id' => 'integer|sales_person_valid',
        'from_email' => 'email',
        'to_email' => 'email',
        'lead_id' => 'integer|exists:website_lead,identifier'
    ];
}