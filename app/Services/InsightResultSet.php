<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Traits\WithGetter;

/**
 * Immutable data structure to deal with insight result-sets.
 *
 * @property array<numeric>|null $subset     the subset of values for a desire aggregate, e.d: a specific manufacturer
 * @property array<numeric>      $complement the universal set of values for a desire aggregate, eg: all manufacturer
 *                                           except a specific manufacturer
 * @property array<string>       $legends
 */
class InsightResultSet
{
    use WithGetter;

    public function __construct(private ?array $subset, private array $complement, private array $legends)
    {
    }
}
