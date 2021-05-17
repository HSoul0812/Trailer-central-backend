<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Models\User\DealerLocation;

class CommonDealerLocationRequest extends Request
{
    use DealerLocationRequestTrait;

    protected function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'id' => 'required|exists:dealer_location,dealer_location_id,dealer_id,' . $this->getDealerId()
        ];
    }

    protected function getObject(): DealerLocation
    {
        return new DealerLocation();
    }

    protected function getObjectIdValue(): int
    {
        return $this->input('id');
    }

    protected function validateObjectBelongsToUser(): bool
    {
        return true;
    }
}
