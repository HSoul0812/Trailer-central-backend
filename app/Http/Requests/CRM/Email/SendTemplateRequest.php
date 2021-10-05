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
        'subject' => 'required|string',
        'to_email' => 'required|email',
        'sales_person_id' => 'nullable|integer|sales_person_valid',
        'from_email' => 'nullable|email|valid_smtp_email'
    ];
}