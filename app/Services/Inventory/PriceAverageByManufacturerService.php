<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Repositories\Inventory\PriceAverageByManufacturerRepositoryInterface;
use App\Services\AbstractAverageByManufacturerService;
use JetBrains\PhpStorm\Pure;

class PriceAverageByManufacturerService extends AbstractAverageByManufacturerService implements PriceAverageByManufacturerServiceInterface
{
    #[Pure]
    public function __construct(private PriceAverageByManufacturerRepositoryInterface $repository)
    {
        parent::__construct($this->repository);
    }
}
