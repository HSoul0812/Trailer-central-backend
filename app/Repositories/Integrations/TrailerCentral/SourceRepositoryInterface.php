<?php

declare(strict_types=1);

namespace App\Repositories\Integrations\TrailerCentral;

use Illuminate\Database\Query\Builder;

/**
 * Describes a generic Trailer Central integration repository.
 */
interface SourceRepositoryInterface
{
    public function queryAllSince(?string $lastDateSynchronized): Builder;

    public function getSerializableColumnsNames(): array;
}
