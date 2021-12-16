<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\CriteriaBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

abstract class AbstractAverageByManufacturerRepository implements AverageByManufacturerRepositoryInterface
{
    public function getAllManufacturers(): Collection
    {
        return DB::table($this->getPerWeekViewName())
            ->select('manufacturer')
            ->distinct()
            ->get();
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string[], [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerDay(CriteriaBuilder $cb): LazyCollection
    {
        $dateRangeFilter = static function (
            \Illuminate\Database\Query\Builder $query,
            \Illuminate\Support\Carbon $from,
            \Illuminate\Support\Carbon $to): \Illuminate\Database\Query\Builder {
            return $query->whereBetween('day', [$from, $to]);
        };

        return $this->getAllFromView($cb, 'day', $this->getPerDayViewName(), $dateRangeFilter);
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string[], [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerWeek(CriteriaBuilder $cb): LazyCollection
    {
        $dateRangeFilter = static function (
            \Illuminate\Database\Query\Builder $query,
            \Illuminate\Support\Carbon $from,
            \Illuminate\Support\Carbon $to): \Illuminate\Database\Query\Builder {
            return $query->whereBetween('week', [
                sprintf('%d-%d', $from->isoWeekYear, $from->isoWeek),
                sprintf('%d-%d', $to->isoWeekYear, $to->isoWeek),
            ]);
        };

        return $this->getAllFromView($cb, 'week', $this->getPerWeekViewName(), $dateRangeFilter);
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string[], [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerMonth(CriteriaBuilder $cb): LazyCollection
    {
        $dateRangeFilter = static function (
            \Illuminate\Database\Query\Builder $query,
            \Illuminate\Support\Carbon $from,
            \Illuminate\Support\Carbon $to): \Illuminate\Database\Query\Builder {
            return $query->whereBetween('month', [
                sprintf('%d-%s', $from->year, $from->format('m')),
                sprintf('%d-%s', $to->year, $to->format('m')),
            ]);
        };

        return $this->getAllFromView($cb, 'month', $this->getPerMonthViewName(), $dateRangeFilter);
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string[], [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerQuarter(CriteriaBuilder $cb): LazyCollection
    {
        $dateRangeFilter = static function (
            \Illuminate\Database\Query\Builder $query,
            \Illuminate\Support\Carbon $from,
            \Illuminate\Support\Carbon $to): \Illuminate\Database\Query\Builder {
            return $query->whereBetween('quarter', [
                sprintf('%d-Q%s', $from->year, $from->quarter),
                sprintf('%d-Q%s', $to->year, $to->quarter),
            ]);
        };

        return $this->getAllFromView($cb, 'quarter', $this->getPerQuarterViewName(), $dateRangeFilter);
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string[], [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerYear(CriteriaBuilder $cb): LazyCollection
    {
        $dateRangeFilter = static function (
            \Illuminate\Database\Query\Builder $query,
            \Illuminate\Support\Carbon $from,
            \Illuminate\Support\Carbon $to): \Illuminate\Database\Query\Builder {
            return $query->whereBetween('year', [$from->year, $to->year]);
        };

        return $this->getAllFromView($cb, 'year', $this->getPerYearViewName(), $dateRangeFilter);
    }

    abstract protected function getPerDayViewName(): string;

    abstract protected function getPerWeekViewName(): string;

    abstract protected function getPerMonthViewName(): string;

    abstract protected function getPerQuarterViewName(): string;

    abstract protected function getPerYearViewName(): string;

    /**
     * @param CriteriaBuilder $cb              {manufacturer:string[], [from]:string[y-m-d], [to]:string[y-m-d]}
     * @param string          $periodColumn    day|week|month|quarter|year
     * @param string          $viewName        the materialized view name
     * @param callable        $dateRangeFilter \Illuminate\Database\Query\Builder callable(
     *                                         \Illuminate\Database\Query\Builder $query,
     *                                         \Illuminate\Support\Carbon         $from,
     *                                         \Illuminate\Support\Carbon         $to
     */
    private function getAllFromView(CriteriaBuilder $cb,
                                    string $periodColumn,
                                    string $viewName,
                                    callable $dateRangeFilter): LazyCollection
    {
        $query = DB::table($viewName)->selectRaw("AVG(aggregate) AS aggregate, $periodColumn AS period");

        if ($cb->isNotBlank('manufacturer')) {
            $query->selectRaw('manufacturer')
                ->whereIn('manufacturer', $cb->get('manufacturer'))
                ->groupBy('manufacturer')
                ->orderBy('manufacturer');
        }

        if ($cb->isNotBlank('not_manufacturer')) {
            $query->whereNotIn('manufacturer', $cb->get('not_manufacturer'));
        }

        if ($cb->isNotBlank('from')) {
            $from = Date::createFromFormat('Y-m-d', $cb->get('from', Date::now()->subYear()->format('Y-m-d')));
            $to = Date::createFromFormat('Y-m-d', $cb->get('to', Date::now()->format('Y-m-d')));

            $query = $dateRangeFilter($query, $from, $to);
        }

        $query->groupBy($periodColumn)->orderBy($periodColumn);

        return $query->cursor();
    }
}
