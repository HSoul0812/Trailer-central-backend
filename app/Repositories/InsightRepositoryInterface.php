<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\CriteriaBuilder;
use Illuminate\Support\Collection;

interface InsightRepositoryInterface
{
    public function getAllPerDay(CriteriaBuilder $cb): Collection;

    public function getAllPerWeek(CriteriaBuilder $cb): Collection;

    public function getAllPeMonth(CriteriaBuilder $cb): Collection;

    public function getAllPerYear(CriteriaBuilder $cb): Collection;
}
