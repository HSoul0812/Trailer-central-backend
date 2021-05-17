<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User\DealerLocationSalesTax;
use App\Repositories\GenericRepository;

interface DealerLocationSalesTaxRepositoryInterface extends GenericRepository
{
    public function getByDealerLocationId(int $dealerLocationId): DealerLocationSalesTax;

    public function create(array $params): DealerLocationSalesTax;

    public function updateOrCreateByDealerLocationId(int $dealerLocationId, array $params): bool;
}
