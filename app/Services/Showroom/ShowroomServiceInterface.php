<?php

namespace App\Services\Showroom;

/**
 * Interface ShowroomServiceInterface
 * @package App\Services\Showroom
 */
interface ShowroomServiceInterface
{
    /**
     * @param array $unit
     * @param array $additionalSearchParams
     * @return array
     */
    public function mapInventoryToFactory(array $unit, array $additionalSearchParams = []): array;
}
