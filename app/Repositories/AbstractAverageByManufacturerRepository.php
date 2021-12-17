<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\NotImplementedException;
use App\Support\CriteriaBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

abstract class AbstractAverageByManufacturerRepository implements AverageByManufacturerRepositoryInterface
{
    public function getAllManufacturersWhichMetStockCriteria(CriteriaBuilder $cb): Collection
    {
        $query = DB::table('inventory_stock_average_per_day')->selectRaw('manufacturer');

        if ($cb->isNotBlank('from')) {
            $from = Date::createFromFormat('Y-m-d', $cb->get('from', Date::now()->subYear()->format('Y-m-d')));
            $to = Date::createFromFormat('Y-m-d', $cb->get('to', Date::now()->format('Y-m-d')));

            $query->whereBetween('day', [$from, $to]);
        }

        $query->groupBy('manufacturer')
            ->havingRaw('AVG(aggregate) >= ?', [config('insights.having_min_avg')])
            ->orderBy('manufacturer');

        return $query->get();
    }

    public function getAllManufacturers(CriteriaBuilder $cb): Collection
    {
        $query = DB::table($this->getViewName($cb->getOrFail('period')))
            ->selectRaw('manufacturer');

        if ($cb->isNotBlank('manufacturers_stock_criteria')) {
            $query->whereIn('manufacturer', $cb->get('manufacturers_stock_criteria'));
        }

        if ($cb->isNotBlank('category')) {
            $query->whereIn('category', $cb->get('category'));
        }

        if ($cb->isNotBlank('from')) {
            $from = Date::createFromFormat('Y-m-d', $cb->get('from', Date::now()->subYear()->format('Y-m-d')));
            $to = Date::createFromFormat('Y-m-d', $cb->get('to', Date::now()->format('Y-m-d')));

            $query = $this->getDateRangeFilter($cb)($query, $from, $to);
        }

        $query->groupBy('manufacturer')->orderBy('manufacturer');

        return $query->get();
        /*
        $query = DB::table('inventory_stock_average_per_day')->selectRaw('manufacturer');

        if ($filterCategories && $cb->isNotBlank('category')) {
            $query->whereIn('category', $cb->get('category'));
        }

        if ($cb->isNotBlank('from')) {
            $from = Date::createFromFormat('Y-m-d', $cb->get('from', Date::now()->subYear()->format('Y-m-d')));
            $to = Date::createFromFormat('Y-m-d', $cb->get('to', Date::now()->format('Y-m-d')));

            $query->whereBetween('day', [$from, $to]);
        }

        $query->groupBy('manufacturer')
            ->havingRaw('AVG(aggregate) >= ?', [config('insights.having_min_avg')])
            ->orderBy('manufacturer');
        //if($filterCategories) $query->dd();
        return $query->get();*/
    }

    /**
     * @param CriteriaBuilder $cb
     * @return Collection
     *
     * @throws NotImplementedException when there is not a view implemented yet for the period
     */
    public function getAllCategories(CriteriaBuilder $cb): Collection
    {
        $query = DB::table($this->getViewName($cb->getOrFail('period')))
            ->selectRaw('category')
            ->whereRaw("trim(category) != ''");

        if ($cb->isNotBlank('manufacturers_stock_criteria')) {
            $query->whereIn('manufacturer', $cb->get('manufacturers_stock_criteria'));
        }

        if ($cb->isNotBlank('from')) {
            $from = Date::createFromFormat('Y-m-d', $cb->get('from', Date::now()->subYear()->format('Y-m-d')));
            $to = Date::createFromFormat('Y-m-d', $cb->get('to', Date::now()->format('Y-m-d')));

            $query = $this->getDateRangeFilter($cb)($query, $from, $to);
        }

        $query->groupBy('category')->orderBy('category');

        return $query->get();
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string[], [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerDay(CriteriaBuilder $cb): LazyCollection
    {
        return $this->getAllFromView($cb, 'day', $this->getPerDayViewName(), $this->getDateRangeFilter($cb));
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string[], [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerWeek(CriteriaBuilder $cb): LazyCollection
    {
        return $this->getAllFromView($cb, 'week', $this->getPerWeekViewName(), $this->getDateRangeFilter($cb));
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string[], [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerMonth(CriteriaBuilder $cb): LazyCollection
    {
        return $this->getAllFromView($cb, 'month', $this->getPerMonthViewName(), $this->getDateRangeFilter($cb));
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string[], [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerQuarter(CriteriaBuilder $cb): LazyCollection
    {
        return $this->getAllFromView($cb, 'quarter', $this->getPerQuarterViewName(), $this->getDateRangeFilter($cb));
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string[], [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerYear(CriteriaBuilder $cb): LazyCollection
    {
        return $this->getAllFromView($cb, 'year', $this->getPerYearViewName(), $this->getDateRangeFilter($cb));
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
    protected function getAllFromView(CriteriaBuilder $cb,
                                    string $periodColumn,
                                    string $viewName,
                                    callable $dateRangeFilter): LazyCollection
    {
        $query = DB::table($viewName)->selectRaw("AVG(aggregate) AS aggregate, $periodColumn AS period");

        if ($cb->isNotBlank('manufacturers_stock_criteria')) {
            $query->whereIn('manufacturer', $cb->get('manufacturers_stock_criteria'));
        }

        if ($cb->isNotBlank('manufacturer')) {
            $query->selectRaw('manufacturer')
                ->whereIn('manufacturer', $cb->get('manufacturer'))
                ->groupBy('manufacturer') // it only should group by manufacturer when the manufacturer has been provided
                ->orderBy('manufacturer');
        }

        if ($cb->isNotBlank('category')) {
            $query->whereIn('category', $cb->get('category'));
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

    protected function getDateRangeFilter(CriteriaBuilder $cb): callable
    {
        $perDayRangeFilter = static function (
            \Illuminate\Database\Query\Builder $query,
            \Illuminate\Support\Carbon $from,
            \Illuminate\Support\Carbon $to): \Illuminate\Database\Query\Builder {
            return $query->whereBetween('day', [$from, $to]);
        };

        return match ($cb->getOrFail('period')) {
            'per_week' => static function (
                \Illuminate\Database\Query\Builder $query,
                \Illuminate\Support\Carbon $from,
                \Illuminate\Support\Carbon $to): \Illuminate\Database\Query\Builder {
                return $query->whereBetween('week', [
                    sprintf('%d-%s', $from->isoWeekYear, $from->isoWeek < 10 ? '0' . $from->isoWeek : $from->isoWeek),
                    sprintf('%d-%s', $to->isoWeekYear, $to->isoWeek < 10 ? '0' . $to->isoWeek : $to->isoWeek),
                ]);
            },
            'per_month' => static function (
                \Illuminate\Database\Query\Builder $query,
                \Illuminate\Support\Carbon $from,
                \Illuminate\Support\Carbon $to): \Illuminate\Database\Query\Builder {
                return $query->whereBetween('month', [
                    sprintf('%d-%s', $from->year, $from->format('m')),
                    sprintf('%d-%s', $to->year, $to->format('m')),
                ]);
            },
            'per_quarter' => static function (
                \Illuminate\Database\Query\Builder $query,
                \Illuminate\Support\Carbon $from,
                \Illuminate\Support\Carbon $to): \Illuminate\Database\Query\Builder {
                return $query->whereBetween('quarter', [
                    sprintf('%d-Q%s', $from->year, $from->quarter),
                    sprintf('%d-Q%s', $to->year, $to->quarter),
                ]);
            },
            'per_year' => static function (
                \Illuminate\Database\Query\Builder $query,
                \Illuminate\Support\Carbon $from,
                \Illuminate\Support\Carbon $to): \Illuminate\Database\Query\Builder {
                return $query->whereBetween('year', [$from->year, $to->year]);
            },
            default => $perDayRangeFilter
        };
    }

    /**
     * @param string $period
     * @return string
     * @throws NotImplementedException when period is not provided
     */
    protected function getViewName(string $period): string
    {
        $methodName = 'get' . ucfirst(Str::camel($period)) . 'ViewName';

        if (method_exists($this, $methodName)) {
            return $this->{$methodName}();
        }

        throw new NotImplementedException(sprintf("'%s' is not implemented in %s", $methodName, get_class($this)));
    }
}
