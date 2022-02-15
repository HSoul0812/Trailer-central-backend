<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

class GetAuditLogDate extends Request {

    protected $rules = [
        'dealer_id' => 'integer|required',
        'year' => 'required|int'
    ];

}
