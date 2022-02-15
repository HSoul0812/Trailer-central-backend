<?php

declare(strict_types=1);

namespace App\Repositories\Dms;

use App\Repositories\GenericRepository;

/**
 * Describes all report queries related with inventories (major units) and parts (they're commonly called stocks)
 */
interface StockRepositoryInterface extends GenericRepository
{
    public const STOCK_TYPE_MIXED = 'mixed';
    public const STOCK_TYPE_PARTS = 'parts';
    public const STOCK_TYPE_INVENTORIES = 'inventories';

    public function financialReport(array $params): array;
}
