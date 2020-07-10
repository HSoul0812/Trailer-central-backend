<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Send Text Request
 * 
 * @author David A Conway Jr.
 */
class SendTextRequest extends Request {

    protected $rules = [
        'lead_id' => 'required|integer',
        'phone' => 'required|string',
        'log_message' => 'required|string',
    ];
}