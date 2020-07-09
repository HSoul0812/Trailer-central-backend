<?php

namespace App\Http\Requests\Dms\Customer;

use App\Http\Requests\Request;


class GetOpenBalanceRequest extends Request {

    protected $rules = [
       'dealer_id' => 'integer',
       'per_page' => 'integer'
    ];
    
}
