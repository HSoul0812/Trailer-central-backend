<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Get Blasts Request
 * 
 * @author David A Conway Jr.
 */
class GetBlastsRequest extends Request {
    
    protected $rules = [
        'campaign_name' => 'required|string',
        'per_page' => 'integer',
        'sort' => 'in:campaign_name,-campaign_name,campaign_subject,-campaign_subject,email_address,-email_address,created_at,-created_at,updated_at,-updated_at',
        'id' => 'array',
        'id.*' => 'integer'
    ];
    
    public function all($keys = null) {
        // Return Result
        $all = parent::all($keys);
        return $all;
    }
}
