<?php

namespace App\Http\Requests\Marketing\Craigslist;

use App\Http\Requests\Request;

class GetBillingRequest extends Request
{
    protected $rules = [
        'per_page' => 'integer',
        'start' => 'required|date_format:Y-m-d\TH:i:s.\0\0\0\Z',
        'end' => 'required|date_format:Y-m-d\TH:i:s.\0\0\0\Z',
        'dealer_id' => 'required|integer',
        'profile_id' => 'required|integer'
    ];
}
