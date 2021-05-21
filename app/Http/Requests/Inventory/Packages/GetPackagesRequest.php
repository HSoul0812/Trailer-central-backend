<?php


namespace App\Http\Requests\Inventory\Packages;

use App\Http\Requests\Request;

/**
 * Class GetPackagesRequest
 * @package App\Http\Requests\Inventory\Packages
 */
class GetPackagesRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|exists:dealer,dealer_id',
        'per_page' => 'integer',
        'page' => 'integer',
    ];
}
