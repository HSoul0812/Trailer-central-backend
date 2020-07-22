<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

class CreateLeadRequest extends Request {
    
    protected $rules = [
        'lead_type' => 'required|array',
        'lead_type.*' => 'lead_type_valid',
        'customer_id' => 'exists:dms_customer,id',
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'email' => 'email',
        'phone_number' => 'regex:/(01)[0-9]{9}/',
        'address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'zip' => 'string',
        'dealer_location_id' => 'exists:dealer_location,dealer_location_id',
        'lead_source' => 'array',
        'lead_source.*' => 'lead_source_valid',
        'lead_status' => 'lead_status_valid',
        'next_contact_date' => 'date_format:Y-m-d H:i:s',
        'contact_type' => 'in:CONTACT,TASK'
    ];
    
}
