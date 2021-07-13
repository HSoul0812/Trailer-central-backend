<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Send Blast Request
 * 
 * @author David A Conway Jr.
 */
class SendBlastRequest extends Request {

    protected $rules = [
        'user_id' => 'required|integer',
        'sales_person_id' => 'nullable|integer|sales_person_valid',
        'leads' => 'required|string'
    ];
}