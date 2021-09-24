<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\CriteriaBuilder;
use Illuminate\Support\Enumerable;

interface InsightRepositoryInterface
{
    public function getAllPerDay(CriteriaBuilder $cb): Enumerable;

    public function getAllPerWeek(CriteriaBuilder $cb): Enumerable;

    public function getAllPeMonth(CriteriaBuilder $cb): Enumerable;

    public function getAllPerYear(CriteriaBuilder $cb): Enumerable;
}
