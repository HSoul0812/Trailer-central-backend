<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User\DealerLocationSalesTaxItem;
use App\Models\User\DealerLocationSalesTaxItemV1;

class DealerLocationSalesTaxItemRepository implements DealerLocationSalesTaxItemRepositoryInterface
{
    public function create(array $params): DealerLocationSalesTaxItem
    {
        $item = new DealerLocationSalesTaxItem();
        $item->fill($params)->save();

        return $item;
    }

    public function deleteByDealerLocationId(int $dealerLocationId): int
    {
        return DealerLocationSalesTaxItem::where(['dealer_location_id' => $dealerLocationId])->delete();
    }

    public function createV1(array $params): DealerLocationSalesTaxItemV1
    {
        $item = new DealerLocationSalesTaxItemV1();
        $item->fill($params)->save();

        return $item;
    }

    public function deleteByDealerLocationIdV1(int $dealerLocationId): int
    {
        return DealerLocationSalesTaxItemV1::where(['dealer_location_id' => $dealerLocationId])->delete();
    }
}
