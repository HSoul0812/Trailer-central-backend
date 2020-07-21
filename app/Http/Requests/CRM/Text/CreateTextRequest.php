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
        'message' => 'required|string',
        'from_number' => 'required|string',
        'to_number' => 'required|string',
        'date_sent' => 'nullable|string'
    ];
}