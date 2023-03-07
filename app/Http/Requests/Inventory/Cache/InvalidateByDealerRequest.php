<?php

namespace App\Http\Requests\Inventory\Cache;

use App\Http\Requests\Request;

class InvalidateByDealerRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'array',
            'dealer_id.*' => 'required|integer|exists:App\Models\User\User,dealer_id',
        ];
    }

    public function dealerIds(): array
    {
        return array_unique($this->input('dealer_id', []));
    }
}
