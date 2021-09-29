<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Services\InsightServiceInterface;
use Illuminate\Support\Collection;

interface AverageByManufacturerServiceInterface extends InsightServiceInterface
{
    public function getAllManufacturers(): Collection;
}
