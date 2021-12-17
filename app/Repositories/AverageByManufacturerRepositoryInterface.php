<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\CriteriaBuilder;
use Illuminate\Support\Collection;

interface AverageByManufacturerRepositoryInterface extends InsightRepositoryInterface
{
    public function getAllManufacturers(CriteriaBuilder $cb): Collection;

    public function getAllCategories(CriteriaBuilder $cb): Collection;
}
