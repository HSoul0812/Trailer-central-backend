<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\StockLog;

interface StockLogRepositoryInterface
{
    public function lastByRecordId(int $recordId): ?StockLog;
}
