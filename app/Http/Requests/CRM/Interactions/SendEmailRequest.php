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
        'user_id' => 'required|integer',
        'lead_id' => 'required|integer',
        'quote_id' => 'nullable|integer',
        'sales_person_id' => 'integer',
        'subject' => 'required|string',
        'body' => 'required|string',
        'files' => 'array',
        'files.*' => 'string',
        // 'files.*' => 'file',

        'existing_attachments' => 'array',
        'existing_attachments.*.filename' => 'string',
        'existing_attachments.*.original_filename' => 'string',
    ];
}