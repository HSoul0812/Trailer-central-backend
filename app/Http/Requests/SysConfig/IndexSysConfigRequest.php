<?php

namespace App\Http\Requests\SysConfig;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IndexSysConfigRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [];
}
