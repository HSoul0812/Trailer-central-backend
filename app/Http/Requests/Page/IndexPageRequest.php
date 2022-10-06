<?php

declare(strict_types=1);

namespace App\Http\Requests\Page;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IndexPageRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
    ];
}
