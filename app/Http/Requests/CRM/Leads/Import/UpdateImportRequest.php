<?php

namespace App\Http\Requests\CRM\Leads\Import;

use App\Http\Requests\Request;
use App\Models\CRM\Leads\LeadImport;

class UpdateImportRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer',
        'emails' => 'required|array',
        'emails.*' => 'required|email'
    ];

    protected function getObject() {
        return new LeadImport();
    }

    protected function getObjectIdValue() {
        return $this->id;
    }

    protected function validateObjectBelongsToUser() {
        return true;
    }

}