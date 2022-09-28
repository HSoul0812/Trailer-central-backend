<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration;

use App\Models\User\Integration\DealerIntegration;
use App\Repositories\GenericRepository;

interface DealerIntegrationRepositoryInterface extends GenericRepository
{
    public function index(array $params);

    public function get(array $params): DealerIntegration;
}
