<?php

namespace App\Nova\Observer;

use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping;

/**
 * Class DealerIncomingMappingObserver
 * @package App\Nova\Observer
 */
class DealerIncomingMappingObserver
{
    /**
     * @param DealerIncomingMapping $dealerIncomingMapping
     */
    public function saving(DealerIncomingMapping $dealerIncomingMapping)
    {
        if ($dealerIncomingMapping->type === DealerIncomingMapping::MANUFACTURER_BRAND) {
            $mapTo = [
                'manufacturer' => $dealerIncomingMapping->map_to_manufacturer,
                'brand' => $dealerIncomingMapping->map_to_brand
            ];

            $dealerIncomingMapping->map_to = json_encode($mapTo);

            unset($dealerIncomingMapping->map_to_brand);
            unset($dealerIncomingMapping->map_to_manufacturer);
        }
    }

    /**
     * @param DealerIncomingMapping $dealerIncomingMapping
     */
    public function retrieved(DealerIncomingMapping $dealerIncomingMapping)
    {
        if ($dealerIncomingMapping->type === DealerIncomingMapping::MANUFACTURER_BRAND) {
            $mapTo = json_decode($dealerIncomingMapping->map_to, true);

            $dealerIncomingMapping->map_to_manufacturer = $mapTo['manufacturer'];
            $dealerIncomingMapping->map_to_brand = $mapTo['brand'];
        }
    }
}
