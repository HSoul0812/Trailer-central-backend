<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Models\Inventory\InventoryLog;

class InventoryLogRepository implements InventoryLogRepositoryInterface
{
    public function lastByRecordId(int $recordId): ?InventoryLog
    {
        return InventoryLog::query()
            ->where('record_id', $recordId)
            ->latest()
            ->first();
    }
}
