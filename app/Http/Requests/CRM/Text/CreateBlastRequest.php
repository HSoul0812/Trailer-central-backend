<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Create Blast Request
 * 
 * @author David A Conway Jr.
 */
class CreateBlastRequest extends Request {

    protected $rules = [
        'template_id' => 'required|integer',
        'campaign_name' => 'required|string',
        'campaign_subject' => 'required|string',
        'from_sms_number' => 'string',
        'action' => 'required|in:inquired,purchased',
        'location_id' => 'nullable|integer',
        'send_after_days' => 'nullable|integer',
        'category' => 'array',
        'category.*' => 'string',
        'brand' => 'array',
        'brand.*' => 'string',
        'include_archived' => 'in:0,-1,1',
        'is_delivered' => 'integer',
        'is_cancelled' => 'integer',
    ];
}