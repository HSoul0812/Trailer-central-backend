<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Update Campaign Request
 *
 * @author David A Conway Jr.
 */
class UpdateCampaignRequest extends Request
{
    protected $rules = [
        'drip_campaigns_id' => 'required|integer|exists:App\Models\CRM\Email\Campaign,drip_campaigns_id',
        'user_id' => 'required|exists:App\Models\User\NewDealerUser,user_id',
        'email_template_id' => 'email_template_exists',
        'campaign_name' => 'string|unique_email_campaign_name',
        'send_after_days' => 'integer',
        'action' => 'campaign_action_valid',
        'unit_categories' => 'nullable|array',
        'unit_categories.*' => 'inventory_cat_valid',
        'brands' => 'nullable|array',
        'brands.*' => 'inventory_mfg_valid',
        'campaign_subject' => 'required|string',
        'include_archived' => 'in:0,-1,1',
        'location_id' => 'nullable|dealer_location_valid',
        'from_email_address' => 'string|nullable',
        'is_enabled' => 'boolean|nullable',
        'update_brands' => 'boolean|nullable',
        'update_categories' => 'boolean|nullable',
    ];

}
