<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User\DealerLocation;

class UpdateDealerLocationRequest extends SaveDealerLocationRequest
{
    protected function getRules(): array
    {
        $rules = [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'id' => 'nullable|integer|exists:dealer_location,dealer_location_id',
            'sales_tax_items.*.id' => 'required_with:sales_tax_items|integer',
            'fees.*.id' => 'required_with:fees|integer',
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
