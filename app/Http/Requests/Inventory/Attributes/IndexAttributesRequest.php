<?php

namespace App\Http\Requests\Inventory\Attributes;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IndexAttributesRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'entity_type_id' => 'required|integer'
    ];
}
