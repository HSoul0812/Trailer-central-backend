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
    ];
}