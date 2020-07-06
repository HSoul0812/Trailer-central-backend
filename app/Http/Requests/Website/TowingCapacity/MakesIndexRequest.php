<?php

namespace App\Http\Requests\Website\TowingCapacity;

use App\Http\Requests\Request;

class MakesIndexRequest extends Request
{
    protected $rules = [
        'year' => 'required|integer|min:2000'
    ];
}
