<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Repositories\Inventory\AverageByManufacturerRepositoryInterface;
use App\Services\InsightResultSet;
use App\Support\CriteriaBuilder;
use Illuminate\Support\Collection;

class AbstractAverageByManufacturerService implements AverageByManufacturerServiceInterface
{
    public function __construct(private AverageByManufacturerRepositoryInterface $repository)
    {
    }

    public function collect(CriteriaBuilder $cb): InsightResultSet
    {
        $criteriaForAll = $cb->except('manufacturer')->addCriteria('not_manufacturer', $cb->get('manufacturer'));

        $manufacturer = $cb->get('manufacturer');

        /** @var array{complement: Collection, subset: Collection} $rawData */
        $rawData = match ($cb->getOrFail('period')) {
            'per_day' => [
                'complement' => $this->repository->getAllPerDay($criteriaForAll),
                'subset'     => !blank($manufacturer) ? $this->repository->getAllPerDay($cb) : [],
            ],
            'per_week' => [
                'complement' => $this->repository->getAllPerWeek($criteriaForAll),
                'subset'     => !blank($manufacturer) ? $this->repository->getAllPerWeek($cb) : [],
            ],
        };

        $complement = [];
        $legends = [];
        $subset = null;

        foreach ($rawData['complement'] as $element) {
            $complement[] = $element->aggregate;
            $legends[] = $element->period;
        }

        if ($rawData['subset']) {
            foreach ($rawData['subset'] as $element) {
                $subset[] = $element->aggregate;
            }
        }

        return new InsightResultSet($subset, $complement, $legends);
    }

    public function getAllManufacturers(): Collection
    {
        return $this->repository->getAllManufacturers();
    }
}
