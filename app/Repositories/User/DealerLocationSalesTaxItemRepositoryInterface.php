<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User\DealerLocationSalesTaxItem;
use App\Models\User\DealerLocationSalesTaxItemV1;
use App\Repositories\GenericRepository;

interface DealerLocationSalesTaxItemRepositoryInterface extends GenericRepository
{
    public function create(array $params): DealerLocationSalesTaxItem;

    public function deleteByDealerLocationId(int $dealerLocationId): int;

    /**
     * for backward compatibility
     */
    public function createV1(array $params): DealerLocationSalesTaxItemV1;

    /**
     * for backward compatibility
     */
    public function deleteByDealerLocationIdV1(int $dealerLocationId): int;
}
