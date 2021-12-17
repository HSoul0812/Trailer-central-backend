<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Repositories\Inventory\StockAverageByManufacturerRepositoryInterface;
use App\Services\AbstractAverageByManufacturerService;
use JetBrains\PhpStorm\Pure;

class StockAverageByManufacturerService extends AbstractAverageByManufacturerService implements StockAverageByManufacturerServiceInterface
{
    #[Pure]
    public function __construct(private StockAverageByManufacturerRepositoryInterface $repository)
    {
        parent::__construct($this->repository);
    }
}
