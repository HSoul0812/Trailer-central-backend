<?php

declare(strict_types=1);

namespace App\Http\Requests\MapService;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class GeocodeRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'q' => 'required',
    ];
}
