<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

class CreateLeadRequest extends Request {
    
    protected $rules = [
        'lead_type' => 'lead_type_valid|required',
        'customer_id' => 'exists:dms_customer,id',
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'email' => 'email|required',
        'phone_number' => 'regex:/(01)[0-9]{9}/',
        'address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'zip' => 'string',
        'lead_status' => 'lead_status_valid',
        'lead_source' => 'lead_source_valid|required',
        'dealer_location_id' => 'exists:dealer_location,dealer_location_id',
        'next_contact_date' => 'date_format:Y-m-d H:i:s'
    ];
    
}
