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
        'lead_id' => 'required|integer',
        'subject' => 'required|string',
        'body'    => 'required|string',
    ];
}