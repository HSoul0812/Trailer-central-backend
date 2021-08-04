<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class CheckDealerLocationRequest extends Request
{
    use DealerLocationRequestTrait;

    protected function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'id' => 'exists:dealer_location,dealer_location_id,dealer_id,' . $this->getDealerId(),
            'name' => 'required|string'
        ];
    }

    public function getName(): string
    {
        return $this->input('name', '');
    }
}
