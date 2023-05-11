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
        $criteriaForAll = $cb->except('manufacturer')
            ->addCriteria('not_manufacturer', $cb->get('manufacturer'))
            ->addCriteria('manufacturers_stock_criteria', $this->getAllManufacturersWhichMetStockCriteria($cb));

        $manufacturer = $cb->get('manufacturer');

        /** @var array{complement: Collection, subset: Collection} $rawData */
        $period = $cb->getOrFail('period');
        $methodName = 'getAll' . Str::of($period)->camel()->ucfirst();

        $rawData = [
            'complement' => $this->repository->{$methodName}($criteriaForAll),
            'subset' => !blank($manufacturer) ? $this->repository->{$methodName}($cb) : [],
        ];

        $complement = [];
        $legends = [];
        $subset = null;

        foreach ($rawData['complement'] as $element) {
            $complement[] = $element->aggregate;
            $legends[] = $element->period;
        }

        $fillLegends = count($legends) === 0;

        if ($rawData['subset']) {
            foreach ($rawData['subset'] as $element) {
                $subset[$element->manufacturer][] = $element->aggregate;

                if ($fillLegends) { // some odd cases which the industry has not data for the period
                    $legends[] = $element->period;
                }
            }
        }

        return new InsightResultSet($subset, $complement, $legends);
    }

    public function getAllManufacturers(CriteriaBuilder $cb): Collection
    {
        $cb = $cb->addCriteria('manufacturers_stock_criteria', $this->getAllManufacturersWhichMetStockCriteria($cb));

        return $this->repository->getAllManufacturers($cb);
    }

    public function getAllCategories(CriteriaBuilder $cb): Collection
    {
        $cb = $cb->addCriteria('manufacturers_stock_criteria', $this->getAllManufacturersWhichMetStockCriteria($cb));

        return $this->repository->getAllCategories($cb);
    }

    protected function getAllManufacturersWhichMetStockCriteria(CriteriaBuilder $cb): array
    {
        return $this->repository->getAllManufacturersWhichMetStockCriteria($cb)
            ->pluck('manufacturer')
            ->toArray();
    }
}
