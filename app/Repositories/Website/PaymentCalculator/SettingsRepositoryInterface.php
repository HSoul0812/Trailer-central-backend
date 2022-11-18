<?php

namespace App\Repositories\Website\PaymentCalculator;

use App\Models\Inventory\Inventory;
use App\Repositories\Repository;

interface SettingsRepositoryInterface extends Repository
{
    public function getCalculatedSettingsByInventory(Inventory $inventory): array;
}
