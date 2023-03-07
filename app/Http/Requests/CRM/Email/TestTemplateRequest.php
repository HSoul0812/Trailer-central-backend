<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Send Template Request
 * 
 * @author David A Conway Jr.
 */
class TestTemplateRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer',
        'user_id' => 'required|integer',
        'subject' => 'required|string',
        'html' => 'required|string',
        'to_email' => 'required|email',
        'sales_person_id' => 'nullable|integer|sales_person_valid'
    ];
}