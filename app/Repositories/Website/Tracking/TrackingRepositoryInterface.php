<?php

namespace App\Repositories\Website\Tracking;

use App\Models\Website\Tracking\Tracking;
use App\Repositories\Repository;

/**
 * Interface TrackingUnitRepositoryInterface
 * @package App\Repositories\Website\Tracking
 */
interface TrackingRepositoryInterface extends Repository
{
    /**
     * @param array $params
     * @return Tracking
     */
    public function find(array $params): ?Tracking;

    /**
     * Update Lead on Tracking
     * 
     * @param array $params
     * @return Tracking
     */
    public function updateTrackLead(string $sessionId, int $leadId): ?Tracking;
}
