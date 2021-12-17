<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Traits\WithGetter;

/**
 * Immutable data structure to deal with insight result-sets.
 *
 * @property array<string, array<numeric>|null $subset the indexed subset of values for a desire aggregate,
 *                                                     e.g: for specific manufacturer as follows
 *                                                     [
 *                                                      'ABC' => [123, 233, 555],
 *                                                      'Terr-ex' => [500, 233, 333],
 *                                                     ]
 * @property array<numeric> $complement the universal set of values for a desire aggregate, eg: all manufacturer
 *                                      except a specific manufacturer
 * @property array<string>  $legends
 */
class InsightResultSet
{
    use WithGetter;

    public function __construct(private ?array $subset, private array $complement, private array $legends)
    {
    }
}
