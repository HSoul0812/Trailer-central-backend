<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AverageByManufacturerRepositoryInterface;
use App\Support\CriteriaBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        $period = $cb->getOrFail('period');
        $methodName = 'getAll' . Str::of($period)->camel()->ucfirst();

        $rawData = [
            'complement' => $this->repository->{$methodName}($criteriaForAll),
            'subset'     => !blank($manufacturer) ? $this->repository->{$methodName}($cb) : [],
        ];

        $complement = [];
        $legends = [];
        $subset = null;

        foreach ($rawData['complement'] as $element) {
            $complement[] = $element->aggregate;
            $legends[] = $element->period;
        }

        if ($rawData['subset']) {
            foreach ($rawData['subset'] as $element) {
                $subset[$element->manufacturer][] = $element->aggregate;
            }
        }

        return new InsightResultSet($subset, $complement, $legends);
    }

    public function getAllManufacturers(CriteriaBuilder $cb): Collection
    {
        return $this->repository->getAllManufacturers($cb);
    }

    public function getAllCategories(CriteriaBuilder $cb): Collection
    {
        return $this->repository->getAllCategories($cb);
    }
}
