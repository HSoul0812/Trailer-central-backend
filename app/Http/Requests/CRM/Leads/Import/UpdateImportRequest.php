<?php

namespace App\Http\Requests\CRM\Leads\Import;

use App\Http\Requests\Request;

class UpdateImportRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer',
        'emails' => 'required|array',
        'emails.*' => 'required|email'
    ];
}