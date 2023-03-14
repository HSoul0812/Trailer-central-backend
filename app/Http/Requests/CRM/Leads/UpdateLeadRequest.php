<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;
use App\Models\CRM\Leads\Lead;

class UpdateLeadRequest extends Request {

    protected $rules = [
        'id' => 'exists:website_lead,identifier',
        'lead_types' => 'array',
        'lead_types.*' => 'string|lead_type_valid',
        'lead_type' => 'string|lead_type_valid',
        'inventory' => 'array',
        'inventory.*' => 'inventory_valid',
        'append_inventory' => 'array',
        'append_inventory.*' => 'inventory_valid',
        'customer_id' => 'exists:dms_customer,id',
        'title' => 'string',
        'referral' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'email_address' => 'email',
        'phone_number' => 'regex:/(0-9)?[0-9]{10}/',
        'address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'zip' => 'string',
        'comments' => 'string',
        'note' => 'string',
        'metadata' => 'string',
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
    ];

    protected function getObject() {
        return new Lead();
    }

    protected function getObjectIdValue() {
        return $this->id;
    }

    protected function validateObjectBelongsToUser() : bool {
        return true;
    }

}
