<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class GetDealerLocationQuoteFeeRequest extends Request
{

    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'id' => 'nullable|integer|exists:dealer_location_quote_fee,dealer_location_id',
    ];
}
