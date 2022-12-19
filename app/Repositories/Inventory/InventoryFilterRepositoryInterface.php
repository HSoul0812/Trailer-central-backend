<?php

namespace App\Repositories\Inventory;

use App\Repositories\Repository;
use App\Models\Inventory\InventoryFilter;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection<InventoryFilter> getAll(array $params = [])
 */
interface InventoryFilterRepositoryInterface extends Repository
{
}
