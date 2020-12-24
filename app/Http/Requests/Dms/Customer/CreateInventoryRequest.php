<?php

declare(strict_types=1);

namespace App\Http\Requests\Dms\Customer;

use App\Http\Requests\Request;

class CreateInventoryRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required',
        'customer_id' => 'integer|min:1|required',
        'inventory_id' => 'integer|min:1|required'
    ];
}
