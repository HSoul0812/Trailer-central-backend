<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration\Specific;

use App\Repositories\GenericRepository;

interface SpecificIntegrationRepositoryInterface extends GenericRepository
{
    /**
     * Gets the specifics values for the dealer integration
     *
     * @param array $params
     * @return array e.e {used_slots: 5}
     */
    public function get(array $params): array;
}
