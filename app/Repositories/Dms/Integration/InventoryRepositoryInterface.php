<?php

declare(strict_types=1);

namespace App\Repositories\Dms\Integration;

use Illuminate\Support\LazyCollection;

/**
 * Describes inventory integration repository.
 */
interface InventoryRepositoryInterface
{
    public function getAllSince(?string $lastDateSynchronized, int $offSet = 0, int $limit = 0): LazyCollection;

    public function getNumberOfRecordsToImport(?string $lastDateSynchronized): int;
}
