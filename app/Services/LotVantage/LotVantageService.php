<?php


namespace App\Services\LotVantage;

use App\Models\Inventory\Inventory;

/**
 * Class LotVantageService
 * @package App\Services\LotVantage
 */
class LotVantageService
{
    public function deleteByInventory(Inventory $inventory)
    {
        return true;
    }
}
