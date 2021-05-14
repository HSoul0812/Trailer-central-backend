<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Show Campaign Request
 * 
 * @author David A Conway Jr.
 */
class ShowCampaignRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}
