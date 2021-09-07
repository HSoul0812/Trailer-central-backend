<?php

declare(strict_types=1);

namespace App\Services\Dms\Integration;

use App\Models\Dms\Integration\SyncProcess;

interface SyncProcessServiceInterface
{
    public function start(string $name): SyncProcess;
}
