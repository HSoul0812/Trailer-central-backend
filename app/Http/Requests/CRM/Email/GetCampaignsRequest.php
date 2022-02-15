<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Get Campaigns Request
 * 
 * @author David A Conway Jr.
 */
class GetCampaignsRequest extends Request {
    
    protected $rules = [
        'campaign_name' => 'string',
        'per_page' => 'integer',
        'sort' => 'in:name,-name,email_address,-email_address,created_at,-created_at,updated_at,-updated_at',
        'id' => 'array',
        'id.*' => 'integer'
    ];
    
    public function all($keys = null) {
        // Return Result
        $all = parent::all($keys);
        return $all;
    }
}
