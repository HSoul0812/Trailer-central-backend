<?php

namespace App\Services\Inventory;

use App\Repositories\Inventory\StockAverageByManufacturerRepositoryInterface;
use App\Support\CriteriaBuilder;
use Illuminate\Support\Collection;

class StockAverageByManufacturerService implements StockAverageByManufacturerServiceInterface
{
    public function __construct(private StockAverageByManufacturerRepositoryInterface $repository)
    {
    }

    /**
     * @return array{all: Collection, aggregate: Collection}
     */
    public function getAll(CriteriaBuilder $cb): array
    {
        $criteriaForAll = $cb->except('manufacturer')->addCriteria('not_manufacturer', $cb->get('manufacturer'));

        return match ($cb->getOrFail('period')) {
            'per_day' => [
                'all'       => $this->repository->getAllPerDay($criteriaForAll),
                'aggregate' => $this->repository->getAllPerDay($cb),
            ],
            'per_week' => [
                'all'       => $this->repository->getAllPerWeek($criteriaForAll),
                'aggregate' => $this->repository->getAllPerWeek($cb),
            ],
        };
    }
}
