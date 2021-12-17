<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Models\Inventory\InventoryLog;

interface InventoryLogRepositoryInterface
{
    public function lastByRecordId(int $recordId): ?InventoryLog;
}
