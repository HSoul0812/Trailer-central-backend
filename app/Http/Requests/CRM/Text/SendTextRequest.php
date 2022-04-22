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
        'log_message' => 'required|string',
        'mediaUrl' => 'array|max:10',
        'mediaUrl.*' => 'url',
    ];
}
