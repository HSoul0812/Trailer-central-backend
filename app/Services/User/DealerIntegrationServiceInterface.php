<?php

declare(strict_types=1);

namespace App\Services\User;

interface DealerIntegrationServiceInterface
{
    /**
     * Gets the specifics values for the dealer integration
     *
     * @param int $id
     * @param int $dealerId
     * @return array e.e {used_slots: 5}
     */
    public function getValues(int $id, int $dealerId): array;
}
