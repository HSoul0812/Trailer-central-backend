<?php

namespace App\Repositories\Website\Tracking;

use App\Repositories\Repository;
use App\Models\Website\Tracking\TrackingUnit;

/**
 * Interface TrackingUnitRepositoryInterface
 * @package App\Repositories\Website\Tracking
 */
interface TrackingUnitRepositoryInterface extends Repository
{

    /**
     * Get Newest Tracking Unit
     * 
     * @param array $params
     * @return null|TrackingUnit
     */
    public function getNewest(array $params): ?TrackingUnit;

    /**
     * Mark Tracking Unit as Inquired
     * 
     * @param string $sessionId
     * @param int $unitId
     * @param string $unitType
     * @return null|TrackingUnit
     */
    public function markUnitInquired(string $sessionId, int $unitId, string $unitType = 'inventory'): ?TrackingUnit;
}