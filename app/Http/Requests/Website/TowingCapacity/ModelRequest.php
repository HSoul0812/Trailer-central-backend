<?php


namespace App\Http\Requests\Website\TowingCapacity;

use App\Http\Requests\Request;

/**
 * Class ModelsRequest
 * @package App\Http\Requests\Website\TowingCapacity
 */
class ModelRequest extends Request
{
    protected $rules = [
        'year' => 'required|integer|min:2000',
        'makeId' => 'required|integer',
    ];
}
