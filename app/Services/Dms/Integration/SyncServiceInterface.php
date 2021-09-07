<?php

declare(strict_types=1);

namespace App\Services\Dms\Integration;

interface SyncServiceInterface
{
    /**
     * @throws \App\Exceptions\Dms\Integration\SyncProcessIsStillWorking
     */
    public function sync(): int;
}
