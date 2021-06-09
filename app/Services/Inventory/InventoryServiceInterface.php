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
     * @return Inventory|null
     */
    public function create(array $params): ?Inventory;

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
     * @param float $costOfUnit
     * @param float $costOfShipping
     * @param float $costOfPrep
     * @param float $costOfRos
     * @return Money
     */
    public function calculateTotalOfCost(float $costOfUnit, float $costOfShipping, float $costOfPrep, float $costOfRos): Money;

    /**
     * @param float $trueCost
     * @param float $costOfShipping
     * @param float $costOfPrep
     * @param float $costOfRos
     * @return Money
     */
    public function calculateTrueTotalCost(float $trueCost, float $costOfShipping, float $costOfPrep, float $costOfRos): Money;

    /**
     * @param float $totalOfCost
     * @param float $pacAmount
     * @param string $pacType
     * @return Money
     */
    public function calculateCostOverhead(float $totalOfCost, float $pacAmount, string $pacType): Money;
}
