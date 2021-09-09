<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\InventoryLog;

interface InventoryLogRepositoryInterface
{
    public function lastByRecordId(int $recordId): ?InventoryLog;
}
