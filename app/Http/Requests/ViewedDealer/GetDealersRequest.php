<?php

namespace App\Http\Requests\ViewedDealer;

use App\Http\Requests\GetDealersRequestInterface;
use App\Http\Requests\Request;

class GetDealersRequest extends Request implements GetDealersRequestInterface
{
    protected array $rules = [
        'state' => 'string',
        'type' => 'integer',
    ];
}
