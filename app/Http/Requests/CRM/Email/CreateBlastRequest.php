<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Create Blast Request
 * 
 * @author David A Conway Jr.
 */
class CreateBlastRequest extends Request {

    protected $rules = [
        'email_template_id' => 'required|email_template_exists',
        'campaign_name' => 'required|string',
        'send_date' => 'required|date_format:Y-m-d H:i:s',
        'from_sms_number' => 'nullable|regex:/(0-9)?[0-9]{10}/',
        'action' => 'required|campaign_action_valid',
        'location_id' => 'nullable|dealer_location_valid',
        'send_after_days' => 'required|integer',
        'category' => 'nullable|array',
        'category.*' => 'inventory_cat_valid',
        'brand' => 'nullable|array',
        'brand.*' => 'inventory_mfg_valid',
        'include_archived' => 'in:0,-1,1',
    ];
}