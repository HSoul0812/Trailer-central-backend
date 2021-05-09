<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User\DealerLocation;

class UpdateDealerLocationRequest extends SaveDealerLocationRequest
{
    protected function getRules(): array
    {
        $rules = [
            'id' => 'required|exists:dealer_location,dealer_location_id,dealer_id,' . $this->getDealerId()
        ];

        return array_merge(parent::getRules(), $rules);
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
