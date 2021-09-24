<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Services\InsightServiceInterface;
use Illuminate\Support\Collection;

interface StockAverageByManufacturerServiceInterface extends InsightServiceInterface
{
    public function getAllManufacturers(): Collection;
}
