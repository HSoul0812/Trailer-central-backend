<?php

namespace App\Http\Requests\Inventory\Packages;

use App\Http\Requests\Request;

/**
 * Class DeletePackageRequest
 * @package App\Http\Requests\Inventory\Packages
 */
class DeletePackageRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|exists:dealer,dealer_id',
        'id' => 'required|exists:packages,id',
    ];
}
