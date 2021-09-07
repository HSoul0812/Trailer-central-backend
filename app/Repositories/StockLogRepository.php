<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\StockLog;

class StockLogRepository implements StockLogRepositoryInterface
{
    public function lastByRecordId(int $recordId): ?StockLog
    {
        return StockLog::query()
            ->where('record_id', $recordId)
            ->latest()
            ->first();
    }
}
