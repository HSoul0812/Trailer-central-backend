<?php

namespace App\Http\Requests\Inventory\Manufacturer;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IndexManufacturerRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'search_term' => 'string|nullable',
        'ids' => 'array',
        'ids.*' => 'integer',
    ];
}
