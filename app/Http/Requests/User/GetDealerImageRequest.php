<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class GetDealerImageRequest extends Request
{
    use DealerLocationRequestTrait;

    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'expired' => 'in:1,0'
    ];
}
