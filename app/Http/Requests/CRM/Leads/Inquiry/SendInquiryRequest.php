<?php

namespace App\Http\Requests\CRM\Leads\Inquiry;

use App\Http\Requests\Request;
use App\Rules\CRM\Leads\BannedLeadTextsRule;
use App\Rules\Inventory\ValidInventoryInquiry;
use App\Rules\CRM\Leads\ValidDealerLocationInquiry;

class SendInquiryRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'required|exists:dealer,dealer_id',
            'website_id' => 'required|website_exists',
            'dealer_location_id' => ['nullable', new ValidDealerLocationInquiry($this->input('website_id'))],
            'inquiry_type' => 'required|inquiry_email_valid',
            'lead_types' => 'required|array',
            'lead_types.*' => 'lead_type_valid',
            'inventory' => 'array',
            'inventory.*' => [new ValidInventoryInquiry($this->input('website_id'))],
            'item_id' => 'nullable|integer',
            'device' => 'nullable|string',
            'title' => 'nullable|string',
            'url' => 'nullable|string',
            'referral' => 'nullable|string',
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'email_address' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'preferred_contact' => 'nullable|in:phone,email',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip' => 'nullable|string',
            'comments' => ['nullable', 'string', new BannedLeadTextsRule()],
            'note' => 'nullable|string',
            'metadata' => 'nullable|json',
            'date_submitted' => 'nullable|date_format:Y-m-d H:i:s',
            'contact_email_sent' => 'nullable|date_format:Y-m-d H:i:s',
            'adf_email_sent' => 'nullable|date_format:Y-m-d H:i:s',
            'cdk_email_sent' => 'nullable|boolean',
            'newsletter' => 'boolean',
            'is_spam' => 'boolean',
            'is_from_classifieds' => 'boolean',
            'lead_source' => 'lead_source_valid',
            'lead_status' => 'lead_status_valid',
            'contact_type' => 'in:CONTACT,TASK',
            'sales_person_id' => 'sales_person_valid',
            'cookie_session_id' => 'nullable|string'
        ];
    }

}
