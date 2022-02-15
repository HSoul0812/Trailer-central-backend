<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Send Campaign Request
 * 
 * @author David A Conway Jr.
 */
class SendCampaignRequest extends Request {

    protected $rules = [
        'user_id' => 'required|integer',
        'sales_person_id' => 'nullable|integer|sales_person_valid',
        'leads' => 'required|string'
    ];
}