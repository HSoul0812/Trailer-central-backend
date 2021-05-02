<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User\DealerLocationSalesTax;

class DealerLocationSalesTaxRepository implements DealerLocationSalesTaxRepositoryInterface
{
    public function getByDealerLocationId(int $dealerLocationId): DealerLocationSalesTax
    {
        return DealerLocationSalesTax::findOrFail($dealerLocationId);
    }

    public function create(array $params): DealerLocationSalesTax
    {
        $taxSettings = new DealerLocationSalesTax();
        $taxSettings->fill($params)->save();

        return $taxSettings;
    }

    public function updateOrCreateByDealerLocationId(int $dealerLocationId, array $params): bool
    {
        $taxSettings = DealerLocationSalesTax::where('dealer_location_id', $dealerLocationId)->first() ??
            new DealerLocationSalesTax();

        return $taxSettings->fill(['dealer_location_id' => $dealerLocationId] + $params)->save();
    }
}
