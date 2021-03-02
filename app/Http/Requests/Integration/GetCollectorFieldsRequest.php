<?php

namespace App\Http\Requests\Integration;

use App\Http\Requests\Request;

/**
 * Class GetCollectorFieldsRequest
 * @package App\Http\Requests\Integration
 */
class GetCollectorFieldsRequest  extends Request
{
    protected $rules = [
        'filter.type.eq' => 'in:item,attribute',
    ];
}
