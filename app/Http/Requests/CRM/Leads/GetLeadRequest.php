<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;
use App\Models\CRM\Leads\Lead;

class GetLeadRequest extends Request {
    
    protected $rules = [
        'id' => 'integer|exists:website_lead,identifier'
    ];
    
    protected function getObject() {
        return new Lead();
    }

    protected function getObjectIdValue() {
        return $this->id;
    }

    protected function validateObjectBelongsToUser(): bool {
        return true;
    }
}
