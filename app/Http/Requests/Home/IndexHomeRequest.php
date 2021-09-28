<?php

declare(strict_types=1);

namespace App\Http\Requests\Home;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IndexHomeRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
    ];
}
