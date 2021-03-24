<?php

declare(strict_types=1);

namespace App\Http\Requests\Dms\Customer;

use App\Http\Requests\Request;

class DeleteInventoryRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'customer_id' => 'integer|min:1|required|exists:dms_customer,id',
        'customer_inventory_ids' => 'array|required',
        'customer_inventory_ids.*' => 'string|max:38,min:36|exists:dms_customer_inventory,uuid'
    ];
}
