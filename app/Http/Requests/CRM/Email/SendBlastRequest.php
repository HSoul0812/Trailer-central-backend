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
        'sales_person_id' => 'integer|sales_person_valid',
        'leads' => 'required|array',
        'leads.*' => 'integer|exists:website_lead,identifier'
    ];
}