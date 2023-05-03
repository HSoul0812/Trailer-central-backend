<?php

namespace App\Http\Requests\IpInfo;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IpInfoRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'ip' => 'ipv4',
    ];
}
