<?php

namespace App\Services\Dms\UnitSale;

/**
 * Interface UnitSaleServiceInterface
 *
 *@package App\Services\Dms\UnitSale
 */
interface UnitSaleServiceInterface
{
    public function bulkArchive(array $params, int $dealerId): bool;
}
