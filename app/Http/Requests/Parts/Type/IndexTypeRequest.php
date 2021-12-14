<?php

declare(strict_types=1);

namespace App\Http\Requests\Parts\Type;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IndexTypeRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
    ];
}
