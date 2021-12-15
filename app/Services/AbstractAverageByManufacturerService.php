<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AverageByManufacturerRepositoryInterface;
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
            'per_month' => [
                'complement' => $this->repository->getAllPerMonth($criteriaForAll),
                'subset'     => !blank($manufacturer) ? $this->repository->getAllPerMonth($cb) : [],
            ],
            'per_quarter' => [
                'complement' => $this->repository->getAllPerQuarter($criteriaForAll),
                'subset'     => !blank($manufacturer) ? $this->repository->getAllPerQuarter($cb) : [],
            ],
            'per_year' => [
                'complement' => $this->repository->getAllPerYear($criteriaForAll),
                'subset'     => !blank($manufacturer) ? $this->repository->getAllPerYear($cb) : [],
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
