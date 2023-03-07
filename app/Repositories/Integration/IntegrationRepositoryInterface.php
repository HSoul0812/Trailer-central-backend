<?php

declare(strict_types=1);

namespace App\Repositories\Integration;

use App\Models\Integration\Integration;
use App\Repositories\GenericRepository;

use Illuminate\Database\Eloquent\Collection;

interface IntegrationRepositoryInterface extends GenericRepository
{
    public function getAll(array $params): Collection;

    public function get(array $params): Integration;
}
