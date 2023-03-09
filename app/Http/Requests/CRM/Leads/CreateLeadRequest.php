<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

class CreateLeadRequest extends Request {

    protected $rules = [
        'lead_types' => 'required|array',
        'lead_types.*' => 'lead_type_valid',
        'website_id' => 'website_valid',
        'inventory' => 'array',
        'inventory.*' => 'inventory_valid',
        'customer_id' => 'exists:dms_customer,id',
        'title' => 'string',
        'referral' => 'string',
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'email_address' => 'email',
        'phone_number' => 'regex:/(0-9)?[0-9]{10}/',
        'address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'zip' => 'string',
        'comments' => 'string',
        'note' => 'string',
        'metadata' => 'string|jotform_enabled',
        'contact_email_sent' => 'date_format:Y-m-d H:i:s',
        'adf_email_sent' => 'date_format:Y-m-d H:i:s',
        'cdk_email_sent' => 'boolean',
        'newsletter' => 'boolean',
        'is_spam' => 'boolean',
        'is_archived' => 'boolean',
        'dealer_location_id' => 'dealer_location_valid',
        'lead_source' => 'string',
        'lead_status' => 'lead_status_valid',
        'next_contact_date' => 'date_format:Y-m-d H:i:s',
        'contact_type' => 'in:CONTACT,TASK',
        'sales_person_id' => 'sales_person_valid',
        'next_followup' => 'nullable|date_format:Y-m-d H:i:s',
        'interaction.type' => 'interaction_type_valid',
        'interaction.note' => 'interaction_note_valid:interaction.type',
        'interaction.time' => 'required_with:interaction.type|date_format:Y-m-d H:i:s'
    ];

}
