<?php

declare(strict_types=1);

namespace App\Repositories\Integrations\TrailerCentral;

use Illuminate\Database\Query\Builder;

/**
 * Describes inventory integration repository.
 */
interface InventoryRepositoryInterface
{
    public function queryAllSince(?string $lastDateSynchronized): Builder;

    public function getSerializableColumnsNames(): array;
}
