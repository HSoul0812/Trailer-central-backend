<?php

namespace App\Repositories\Website\Tracking;

use App\Repositories\Repository;
use App\Models\Website\Website\Tracking\TrackingUnit;

/**
 * Interface TrackingUnitRepositoryInterface
 * @package App\Repositories\Website\Tracking
 */
interface TrackingUnitRepositoryInterface extends Repository
{
    /**
     * Mark Tracking Unit as Inquired
     * 
     * @param string $sessionId
     * @param int $unitId
     * @param string $unitType
     * @return TrackingUnit
     */
    public function markUnitInquired(string $sessionId, int $unitId, string $unitType = 'inventory'): TrackingUnit;
}