<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;
use App\Models\CRM\Leads\Lead;

class UpdateLeadRequest extends Request {

    protected $rules = [
        'id' => 'exists:website_lead,identifier',
        'lead_type' => 'array',
        'lead_type.*' => 'lead_type_valid',
        'website_id' => 'exists:website,id',
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
        'dealer_location_id' => 'exists:dealer_location,dealer_location_id',
        'lead_source' => 'lead_source_valid',
        'lead_status' => 'lead_status_valid',
        'next_contact_date' => 'date_format:Y-m-d H:i:s',
        'contact_type' => 'in:CONTACT,TASK',
        'sales_person_id' => 'exists:crm_sales_person,id'
    ];

    protected function getObject() {
        return new Lead();
    }

    protected function getObjectIdValue() {
        return $this->id;
    }

    protected function validateObjectBelongsToUser() {
        return true;
    }

}