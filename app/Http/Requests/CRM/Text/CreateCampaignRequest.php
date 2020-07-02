<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Create Campaign Request
 * 
 * @author David A Conway Jr.
 */
class CreateCampaignRequest extends Request {

    protected $rules = [
        'template_id' => 'required|integer',
        'campaign_name' => 'required|string',
        'campaign_subject' => 'required|string',
        'text_number' => 'required|string',
        'action' => 'in:inquired,purchased',
        'location_id' => 'nullable|integer',
        'send_after_days' => 'nullable|integer',
        'category' => 'nullable|string',
        'brand' => 'nullable|string',
        'include_archived' => 'in:0,-1,1',
        'is_enabled' => 'integer',
    ];
}