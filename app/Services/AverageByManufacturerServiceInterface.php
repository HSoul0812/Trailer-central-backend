<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\CriteriaBuilder;
use Illuminate\Support\Collection;

interface AverageByManufacturerServiceInterface extends InsightServiceInterface
{
    public function getAllManufacturers(CriteriaBuilder $cb): Collection;

    public function getAllCategories(CriteriaBuilder $cb): Collection;
}
