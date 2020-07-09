<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;
use App\Models\CRM\Leads\Lead;
use Illuminate\Database\Eloquent\Model;

class UpdateLeadRequest extends Request {
    
    protected $rules = [
        'id' => 'exists:website_lead,identifier',
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
