<?php

namespace App\Http\Requests\Website\TowingCapacity;

use App\Http\Requests\Request;

/**
 * Class VehiclesRequest
 * @package App\Http\Requests\Website\TowingCapacity
 */
class VehiclesRequest extends Request
{
    protected $rules = [
        'year' => 'required|integer|min:2000',
        'makeId' => 'required|integer',
        'model' => 'required|string|max:255',
    ];
}
