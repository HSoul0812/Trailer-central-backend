<?php

namespace App\Http\Requests\Inventory\Cache;

use App\Http\Requests\Request;

class InvalidateByDealerRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        ];
    }
}
