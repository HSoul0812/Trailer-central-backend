<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\CriteriaBuilder;

interface InsightServiceInterface
{
    public function collect(CriteriaBuilder $cb): InsightResultSet;
}
