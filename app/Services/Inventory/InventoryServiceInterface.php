<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Inventory;
use Brick\Money\Money;

/**
 * Interface InventoryServiceInterface
 * @package App\Services\Inventory
 */
interface InventoryServiceInterface
{
    /**
     * @param array $params
     * @return Inventory
     */
    public function create(array $params): Inventory;

    /**
     * @param array $params
     * @return Inventory
     */
    public function update(array $params): Inventory;

    /**
     * @param int $inventoryId
     * @return bool
     */
    public function delete(int $inventoryId): bool;

    /**
     * @param int $dealerId
     * @return array
     */
    public function deleteDuplicates(int $dealerId): array;

    /**
     * @return array
     */
    public function archiveSoldItems(): array;

    /**
     * @param int $inventoryId
     * @return float
     */
    public function deliveryPrice(int $inventoryId): float;

}
