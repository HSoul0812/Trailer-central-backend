<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Show Text Request
 * 
 * @author David A Conway Jr.
 */
class StopTextRequest extends Request {
    
    protected $rules = [
        'sms_number' => 'required|regex:/(0-9)?[0-9]{10}/',
        'lead_id' => 'lead_exists',
        'text_id' => 'text_exists'
    ];
    
}
