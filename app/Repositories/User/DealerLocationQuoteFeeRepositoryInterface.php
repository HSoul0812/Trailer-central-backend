<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Repositories\GenericRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Describes the location fee repository implementation
 */
interface DealerLocationQuoteFeeRepositoryInterface extends GenericRepository
{

    public function getAll(array $params): LengthAwarePaginator;

    public function getByDealerLocationId(int $id, array $extraParams = []): LengthAwarePaginator;
}
