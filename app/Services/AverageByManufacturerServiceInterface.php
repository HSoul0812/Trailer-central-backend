<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;

interface AverageByManufacturerServiceInterface extends InsightServiceInterface
{
    public function getAllManufacturers(): Collection;
}
