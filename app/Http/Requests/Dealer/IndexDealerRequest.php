<?php

namespace App\Http\Requests\Dealer;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IndexDealerRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'state' => 'string',
        'type' => 'integer',
    ];
}
