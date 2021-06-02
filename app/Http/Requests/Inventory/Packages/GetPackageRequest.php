<?php

namespace App\Http\Requests\Inventory\Packages;

use App\Http\Requests\Request;

/**
 * Class GetPackageRequest
 * @package App\Http\Requests\Inventory\Packages
 */
class GetPackageRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|exists:dealer,dealer_id',
        'id' => 'integer|required',
    ];
}
