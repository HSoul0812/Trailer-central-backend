<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\CriteriaBuilder;
use Illuminate\Support\Collection;

interface InsightServiceInterface
{
    /**
     * @return array{all: Collection, aggregate: Collection}
     */
    public function getAll(CriteriaBuilder $cb): array;
}
