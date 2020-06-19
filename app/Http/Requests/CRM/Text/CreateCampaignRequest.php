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
        'from_email_address' => 'required|string',
        'action' => 'in:inquired,purchased',
        'location_id' => 'nullable|integer',
        'send_after_days' => 'nullable|integer',
        'unit_category' => 'nullable|string',
        'include_archived' => 'in:0,-1,1',
        'is_enabled' => 'integer',
    ];
}