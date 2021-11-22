<?php
namespace App\Repositories\Dms;

use App\Repositories\Repository;

interface UnitSaleRepositoryInterface extends Repository
{
    public function getTotalReceived(int $unitSaleId, ?array $params);
}