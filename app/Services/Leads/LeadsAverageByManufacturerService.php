<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Repositories\Leads\LeadsAverageByManufacturerRepositoryInterface;
use App\Services\AbstractAverageByManufacturerService;
use JetBrains\PhpStorm\Pure;

class LeadsAverageByManufacturerService extends AbstractAverageByManufacturerService implements LeadsAverageByManufacturerServiceInterface
{
    #[Pure]
    public function __construct(private LeadsAverageByManufacturerRepositoryInterface $repository)
    {
        parent::__construct($this->repository);
    }
}
