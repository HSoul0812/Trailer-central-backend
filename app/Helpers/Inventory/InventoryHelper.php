<?php

namespace App\Helpers\Inventory;

use App\Models\Inventory\InventoryImage;
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
     * @param  float  $costOfUnit
     * @param  float  $costOfShipping
     * @param  float  $costOfPrep
     * @param  float  $costOfRos
     * @return Money
     */
    public function calculateTotalOfCost(
        float $costOfUnit,
        float $costOfShipping,
        float $costOfPrep,
        float $costOfRos
    ): Money {
        return Money::of($costOfUnit + $costOfShipping + $costOfPrep + $costOfRos, 'USD', null, RoundingMode::DOWN);
    }

    /**
     * @param  float  $trueCost
     * @param  float  $costOfShipping
     * @param  float  $costOfPrep
     * @param  float  $costOfRos
     * @return Money
     */
    public function calculateTrueTotalCost(
        float $trueCost,
        float $costOfShipping,
        float $costOfPrep,
        float $costOfRos
    ): Money {
        return Money::of($trueCost + $costOfShipping + $costOfPrep + $costOfRos, 'USD', null, RoundingMode::DOWN);
    }

    /**
     * @param  float  $totalOfCost
     * @param  float  $pacAmount
     * @param  string  $pacType
     * @return Money
     */
    public function calculateCostOverhead(float $totalOfCost, float $pacAmount, string $pacType): Money
    {
        $pacActualAmount = $pacType === DealerLocation::PAC_TYPE_PERCENT ? ($totalOfCost * $pacAmount) / 100 : $pacAmount;

        return Money::of($totalOfCost + $pacActualAmount, 'USD', null, RoundingMode::DOWN);
    }

    /**
     * Sorts the inventory images ensuring that the image which is `is_default=1` always will be the first image,
     * also, if the image has NULL as position, then, that image will be sorted at last position.
     *
     * That sorting strategy was extracted from the ES worker.
     *
     * @return callable
     */
    public function imageSorter(): callable
    {
        return static function (InventoryImage $image): int {
            // when the position is null, it will sorted a last position
            $position = $image->position ?? InventoryImage::LAST_IMAGE_POSITION;

            return $image->isDefault() ? InventoryImage::FIRST_IMAGE_POSITION_EDGE_CASE : $position;
        };
    }

    /**
     * To avoid to refactor many things
     *
     * @return static
     */
    public static function singleton(): self
    {
        static $self;

        if (!$self) {
            $self = new self();
        }

        return $self;
    }
}
