<?php

declare(strict_types=1);

namespace App\Services\TrailerCentral\Integration;

use App\Models\TrailerCentral\Integration\SyncProcess;
use App\Repositories\TrailerCentral\Integration\SyncProcessRepositoryInterface;

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
