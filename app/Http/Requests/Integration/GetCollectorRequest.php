<?php

namespace App\Http\Requests\Integration;

use App\Http\Requests\Request;

/**
 * Class GetCollectorRequest
 * @package App\Http\Requests\Integration
 */
class GetCollectorRequest extends Request
{
    protected $rules = [
        'active' => 'boolean',
    ];
}
