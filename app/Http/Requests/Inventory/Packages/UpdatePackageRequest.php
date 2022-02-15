<?php

namespace App\Http\Requests\Inventory\Packages;

use App\Http\Requests\Request;

/**
 * Class UpdatePackageRequest
 * @package App\Http\Requests\Inventory\Packages
 */
class UpdatePackageRequest extends Request
{
    protected $rules = [
        'id' => 'required|exists:packages,id',
        'dealer_id' => 'required|exists:dealer,dealer_id',
        'visible_with_main_item' => 'boolean',

        'inventories' => 'required|array|filled',
        'inventories.*.inventory_id' => 'integer|required|exists:inventory,inventory_id',
        'inventories.*.is_main_item' => 'boolean',
    ];
}
