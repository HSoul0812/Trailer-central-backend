<?php

declare(strict_types=1);

namespace App\Services\Quickbooks;

use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;

interface DealerLocationServiceInterface
{
    public function update(int $dealerLocationId): ?QuickbookApproval;

    public function reindexAndInvalidateCacheInventory(int $dealerLocationId): void;
}
