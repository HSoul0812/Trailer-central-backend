<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User\DealerLocation;

class UpdateDealerLocationRequest extends SaveDealerLocationRequest
{
    /** @var int */
    public $id;

    protected function getRules(): array
    {
        $rules = [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'id' => 'nullable|integer|exists:dealer_location,dealer_location_id',
        ];

        return array_merge($rules, parent::getRules());
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
