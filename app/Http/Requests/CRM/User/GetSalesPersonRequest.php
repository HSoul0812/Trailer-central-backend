<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class GetSalesPersonRequest extends Request {

    protected $rules = [
        'id' => 'required|integer'
    ];

}
