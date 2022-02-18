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
        'template_id' => 'required|text_template_exists',
        'campaign_name' => 'required|string|unique_text_blast_campaign_name',
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
