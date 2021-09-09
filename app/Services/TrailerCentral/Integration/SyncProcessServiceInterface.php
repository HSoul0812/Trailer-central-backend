<?php

declare(strict_types=1);

namespace App\Services\TrailerCentral\Integration;

use App\Models\TrailerCentral\Integration\SyncProcess;

interface SyncProcessServiceInterface
{
    public function start(string $name): SyncProcess;
}
