<?php

namespace App\Http\Requests\Inventory\Packages;

use App\Http\Requests\Request;

/**
 * Class CreatePackageRequest
 * @package App\Http\Requests\Inventory\Packages
 */
class CreatePackageRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|exists:dealer,dealer_id',
        'visible_with_main_item' => 'boolean',

        'inventories' => 'required|array|filled',
        'inventories.*.inventory_id' => 'integer|required|exists:inventory,inventory_id',
        'inventories.*.is_main_item' => 'boolean',
    ];
}
