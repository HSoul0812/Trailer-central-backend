<?php

namespace App\Http\Requests\Inventory\Brand;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IndexBrandRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'dealer_id' => 'array',
        'dealer_id.*' => 'integer',
    ];
}
