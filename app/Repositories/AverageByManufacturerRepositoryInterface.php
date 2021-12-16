<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface AverageByManufacturerRepositoryInterface extends InsightRepositoryInterface
{
    public function getAllManufacturers(): Collection;

    public function getAllCategories(): Collection;
}
