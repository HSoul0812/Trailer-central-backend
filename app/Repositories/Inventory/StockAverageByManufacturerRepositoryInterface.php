<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Repositories\InsightRepositoryInterface;
use Illuminate\Support\Collection;

interface StockAverageByManufacturerRepositoryInterface extends InsightRepositoryInterface
{
    public function getAllManufacturers(): Collection;
}
