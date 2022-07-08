<?php

namespace App\Http\Requests\Dms\Quotes;

use App\Http\Requests\Request;

class GetQuoteRefundsRequest extends Request
{
    protected $rules = [
        'sort' => 'string',
        'with' => 'string',
        'per_page' => 'required|int|max:500',
        'page' => 'required|int',
        'register_id' => 'int',
        'customer_id' => 'int',
        
        // This needs to be in the format like this
        // 2022-08-25 00:00:00, 2022-08-31 23:59:59
        'created_at_between' => 'string',
    ];
}