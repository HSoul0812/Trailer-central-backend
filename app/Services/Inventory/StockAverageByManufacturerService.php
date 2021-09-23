<?php

namespace App\Services\Inventory;

use App\Repositories\Inventory\StockAverageByManufacturerRepositoryInterface;
use App\Services\InsightResultSet;
use App\Support\CriteriaBuilder;

class StockAverageByManufacturerService implements StockAverageByManufacturerServiceInterface
{
    public function __construct(private StockAverageByManufacturerRepositoryInterface $repository)
    {
    }

    public function collect(CriteriaBuilder $cb): InsightResultSet
    {
        $criteriaForAll = $cb->except('manufacturer')->addCriteria('not_manufacturer', $cb->get('manufacturer'));

        $data = match ($cb->getOrFail('period')) {
            'per_day' => [
                'complement' => $this->repository->getAllPerDay($criteriaForAll),
                'subset'     => $this->repository->getAllPerDay($cb),
            ],
            'per_week' => [
                'complement' => $this->repository->getAllPerWeek($criteriaForAll),
                'subset'     => $this->repository->getAllPerWeek($cb),
            ],
        };

        $complement = [];
        $subset = [];
        $legends = [];

        foreach ($data['complement'] as $element) {
            $complement[] = $element->stock;
            $legends[] = $element->period;
        }

        foreach ($data['subset'] as $element) {
            $subset[] = $element->stock;
        }

        return new InsightResultSet($complement, $subset, $legends);
    }
}
