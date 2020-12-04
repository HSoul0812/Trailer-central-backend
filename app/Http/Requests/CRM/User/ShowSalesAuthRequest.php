<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class ShowSalesAuthRequest extends Request {

    protected $rules = [
        'id' => 'required|integer',
        'token_type' => 'required|valid_token_type'
    ];

}
