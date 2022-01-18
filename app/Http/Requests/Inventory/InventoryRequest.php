<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class InventoryRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [

    ];
}
