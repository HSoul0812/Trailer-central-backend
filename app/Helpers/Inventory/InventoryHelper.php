<?php

namespace App\Helpers\Inventory;

use App\Models\User\DealerLocation;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

/**
 * Class InventoryHelper
 * @package App\Helpers\Inventory
 */
class InventoryHelper
{
    /**
     * @param float $costOfUnit
     * @param float $costOfShipping
     * @param float $costOfPrep
     * @param float $costOfRos
     * @return Money
     */
    public function calculateTotalOfCost(float $costOfUnit, float $costOfShipping, float $costOfPrep, float $costOfRos): Money
    {
        return Money::of($costOfUnit + $costOfShipping + $costOfPrep + $costOfRos, 'USD', null, RoundingMode::DOWN);
    }

    /**
     * @param float $trueCost
     * @param float $costOfShipping
     * @param float $costOfPrep
     * @param float $costOfRos
     * @return Money
     */
    public function calculateTrueTotalCost(float $trueCost, float $costOfShipping, float $costOfPrep, float $costOfRos): Money
    {
        return  Money::of($trueCost + $costOfShipping + $costOfPrep + $costOfRos, 'USD', null, RoundingMode::DOWN);
    }

    /**
     * @param float $totalOfCost
     * @param float $pacAmount
     * @param string $pacType
     * @return Money
     */
    public function calculateCostOverhead(float $totalOfCost, float $pacAmount, string $pacType): Money
    {
        $pacActualAmount = $pacType === DealerLocation::PAC_TYPE_PERCENT ? ($totalOfCost * $pacAmount) / 100 : $pacAmount;

        return Money::of($totalOfCost + $pacActualAmount, 'USD', null, RoundingMode::DOWN);
    }
}
