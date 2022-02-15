<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class CreateDealerLocationMileageFeeRequest extends Request
{
    protected $rules = [
        'dealer_location_id' => 'required|exists:dealer_location',
        'inventory_category_id' => 'required|exists:inventory_category',
        'fee_per_mile' => 'required|numeric'
    ];
}
