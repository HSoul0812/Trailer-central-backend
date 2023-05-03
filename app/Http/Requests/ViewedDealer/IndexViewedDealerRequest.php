<?php

namespace App\Http\Requests\ViewedDealer;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IndexViewedDealerRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'name' => 'required|string',
    ];
}
