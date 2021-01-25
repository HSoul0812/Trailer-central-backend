<?php

namespace App\Http\Requests\CRM\Leads\Import;

use App\Http\Requests\Request;

class DeleteImportRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer',
        'email' => 'email'
    ];

}