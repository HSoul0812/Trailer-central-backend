<?php

namespace App\Http\Requests\CRM\Interactions;

use App\Http\Requests\Request;

/**
 * Send Email Request
 * 
 * @author David A Conway Jr.
 */
class SendEmailRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer',
        'sales_person_id' => 'nullable|integer',
        'lead_id' => 'required|integer',
        'subject' => 'required|string',
        'body' => 'required|string',
        'files' => 'array',
        'files.*' => 'string',
        'attachments' => 'array'
    ];
}