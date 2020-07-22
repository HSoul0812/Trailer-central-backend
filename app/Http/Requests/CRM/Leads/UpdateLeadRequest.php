<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;
use App\Models\CRM\Leads\Lead;

class UpdateLeadRequest extends Request {
    
    protected $rules = [
        'id' => 'exists:website_lead,identifier',
        'lead_type' => 'array',
        'lead_type.*' => 'lead_type_valid',
        'customer_id' => 'exists:dms_customer,id',
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'email',
        'phone_number' => 'regex:/(01)[0-9]{9}/',
        'address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'zip' => 'string',
        'dealer_location_id' => 'exists:dealer_location,dealer_location_id',
        'lead_source' => 'lead_source_valid',
        'lead_status' => 'lead_status_valid',
        'next_contact_date' => 'date_format:Y-m-d H:i:s',
        'contact_type' => 'in:CONTACT,TASK'        
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
