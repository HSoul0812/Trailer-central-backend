<?php

namespace App\Repositories\Website\PaymentCalculator;

use App\Models\Inventory\Inventory;
use App\Repositories\Repository;

interface SettingsRepositoryInterface extends Repository
{
    /**
     * @param Inventory $inventory
     * @return array{apr: float, down: float, years: int, months: int, monthly_payment: float, down_percentage:float}
     */
    public function getCalculatedSettingsByInventory(Inventory $inventory): array;
}
