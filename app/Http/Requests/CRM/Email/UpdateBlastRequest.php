<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Update Blast Request
 * 
 * @author David A Conway Jr.
 */
class UpdateBlastRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer',
        'email_template_id' => 'email_template_exists',
        'campaign_name' => 'string',
        'send_date' => 'date_format:Y-m-d H:i:s',
        'from_sms_number' => 'nullable|regex:/(0-9)?[0-9]{10}/',
        'action' => 'campaign_action_valid',
        'location_id' => 'nullable|dealer_location_valid',
        'send_after_days' => 'integer',
        'category' => 'nullable|array',
        'category.*' => 'inventory_cat_valid',
        'brand' => 'nullable|array',
        'brand.*' => 'inventory_mfg_valid',
        'include_archived' => 'in:0,-1,1',
        'is_delivered' => 'nullable|boolean',
        'is_cancelled' => 'nullable|boolean',
    ];
    
}