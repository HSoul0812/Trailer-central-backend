<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration;

use App\Models\User\Integration\DealerIntegration;
use App\Repositories\GenericRepository;

use Illuminate\Database\Eloquent\Collection;

interface DealerIntegrationRepositoryInterface extends GenericRepository
{
    public function getAll(array $params): Collection;

    public function get(array $params): DealerIntegration;

    public function update(array $params): DealerIntegration;

    public function delete(array $params): DealerIntegration;
}
