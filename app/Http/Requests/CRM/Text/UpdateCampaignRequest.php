<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Update Campaign Request
 * 
 * @author David A Conway Jr.
 */
class UpdateCampaignRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer',
        'template_id' => 'integer',
        'campaign_name' => 'string',
        'campaign_subject' => 'string',
        'from_email_address' => 'string',
        'action' => 'in:inquired,purchased',
        'location_id' => 'nullable|integer',
        'send_after_days' => 'nullable|integer',
        'category' => 'nullable|string',
        'brand' => 'nullable|string',
        'include_archived' => 'in:0,-1,1',
        'is_enabled' => 'integer',
    ];
    
}