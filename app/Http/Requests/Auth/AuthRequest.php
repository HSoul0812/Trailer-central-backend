<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class AuthRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [];
}
