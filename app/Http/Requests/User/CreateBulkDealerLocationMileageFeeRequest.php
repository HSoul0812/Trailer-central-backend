<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class CreateBulkDealerLocationMileageFeeRequest extends Request
{
    protected $rules = [
        'dealer_location_id' => 'required|exists:dealer_location',
        'fee_per_mile' => 'required|numeric'
    ];
}
