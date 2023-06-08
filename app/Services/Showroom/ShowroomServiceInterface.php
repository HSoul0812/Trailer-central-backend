<?php

namespace App\Services\Showroom;

/**
 * Interface ShowroomServiceInterface
 * @package App\Services\Showroom
 */
interface ShowroomServiceInterface
{
    public const REWRITABLE_FIELDS_OPTION = 'rewritableFields';

    /**
     * @param array $unit
     * @param array $additionalSearchParams
     * @param array $options
     * @return array
     */
    public function mapInventoryToFactory(array $unit, array $additionalSearchParams = [], array $options = []): array;
}
