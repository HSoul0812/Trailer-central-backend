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
        'send_date' => 'required|string',
        'from_sms_number' => 'nullable|string',
        'action' => 'required|in:inquired,purchased',
        'location_id' => 'nullable|integer',
        'send_after_days' => 'required|integer',
        'category' => 'nullable|array',
        'category.*' => 'nullable|string',
        'brand' => 'nullable|array',
        'brand.*' => 'nullable|string',
        'include_archived' => 'in:0,-1,1',
        'is_delivered' => 'nullable|integer',
        'is_cancelled' => 'nullable|integer',
    ];
}