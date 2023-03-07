<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Create Blast Request
 *
 * @author David A Conway Jr.
 */
class CreateBlastRequest extends Request
{
    protected $rules = [
        'email_template_id' => 'required|email_template_exists',
        'location_id' => 'nullable|dealer_location_valid',
        'send_after_days' => 'required|integer',
        'action' => 'required|campaign_action_valid',
        'campaign_name' => 'required|string|unique_email_blast_name',
        'user_id' => 'required|exists:App\Models\User\NewDealerUser,user_id',
        'from_email_address' => 'string|nullable',
        'campaign_subject' => 'required|string',
        'include_archived' => 'nullable|in:0,-1,1',
        'send_date' => 'required|date_format:Y-m-d H:i:s',
        'delivered' => 'nullable|boolean',
        'unit_categories' => 'nullable|array',
        'unit_categories.*' => 'inventory_cat_valid',
        'brands' => 'nullable|array',
        'brands.*' => 'inventory_mfg_valid',
    ];

    /**
     * @return string[]
     */
    protected function getAttributeNames(): array
    {
        return [
            'campaign_name' => 'blast name',
            'campaign_subject' => 'blast subject',
        ];
    }
}
