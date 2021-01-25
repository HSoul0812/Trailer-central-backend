<?php

namespace App\Http\Requests\CRM\Leads\Import;

use App\Http\Requests\Request;

class GetImportsRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer',
        'sort' => 'in:email,-email,created_at,-created_at,updated_at,-updated_at'
    ];

}