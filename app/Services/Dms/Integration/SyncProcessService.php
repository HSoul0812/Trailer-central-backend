<?php

declare(strict_types=1);

namespace App\Services\Dms\Integration;

use App\Models\Dms\Integration\SyncProcess;
use App\Repositories\Dms\Integration\SyncProcessRepositoryInterface;

class SyncProcessService implements SyncProcessServiceInterface
{
    public function __construct(private SyncProcessRepositoryInterface $repository)
    {
    }

    public function start(string $name): SyncProcess
    {
        return $this->repository->create(['name' => $name]);
    }
}
