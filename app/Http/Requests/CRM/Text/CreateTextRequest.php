<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Create Text Request
 * 
 * @author David A Conway Jr.
 */
class CreateTextRequest extends Request {

    protected $rules = [
        'log_message' => 'required|string',
        'from_number' => 'required|regex:/(0-9)?[0-9]{10}/',
        'to_number' => 'required|regex:/(0-9)?[0-9]{10}/',
        'date_sent' => 'nullable|date_format:Y-m-d H:i:s'
    ];
}