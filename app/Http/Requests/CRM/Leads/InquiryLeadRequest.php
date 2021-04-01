<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

class InquiryLeadRequest extends Request {

    protected $rules = [
        'website_id' => 'required|website_valid',
        'dealer_location_id' => 'required|dealer_location_valid',
        'inquiry_type' => 'required|inquiry_type_valid',
        'lead_types' => 'required|array',
        'lead_types.*' => 'lead_type_valid',
        'inventory' => 'array',
        'inventory.*' => 'inventory_valid',
        'device' => 'string',
        'title' => 'string',
        'referral' => 'string',
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'email_address' => 'email',
        'phone_number' => 'regex:/(0-9)?[0-9]{10}/',
        'preferred_contact' => 'in:phone,email',
        'address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'zip' => 'string',
        'comments' => 'string',
        'note' => 'string',
        'metadata' => 'json',
        'date_submitted' => 'nullable|date_format:Y-m-d H:i:s',
        'contact_email_sent' => 'nullable|date_format:Y-m-d H:i:s',
        'adf_email_sent' => 'nullable|date_format:Y-m-d H:i:s',
        'cdk_email_sent' => 'nullable|boolean',
        'newsletter' => 'boolean',
        'is_spam' => 'boolean',
        'lead_source' => 'lead_source_valid',
        'lead_status' => 'lead_status_valid',
        'contact_type' => 'in:CONTACT,TASK',
        'sales_person_id' => 'sales_person_valid'
    ];

}