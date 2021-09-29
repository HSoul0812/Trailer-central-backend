<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
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
     * @param CriteriaBuilder $cb {manufacturer:string, [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerDay(CriteriaBuilder $cb): LazyCollection
    {
        $query = DB::table($this->getPerDayViewName())->selectRaw('AVG(aggregate) AS aggregate, day AS period');

        if ($cb->isNotBlank('manufacturer')) {
            $query->where('manufacturer', $cb->get('manufacturer'));
        }

        if ($cb->isNotBlank('not_manufacturer')) {
            $query->where('manufacturer', '!=', $cb->get('not_manufacturer'));
        }

        if ($cb->isNotBlank('from')) {
            $from = $cb->get('from');
            $to = $cb->get('to', Date::now()->format('y-m-d'));

            $query->whereBetween('day', [$from, $to]);
        }

        $query->groupBy('day')->orderBy('day');

        return $query->cursor();
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string, [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerWeek(CriteriaBuilder $cb): LazyCollection
    {
        $query = DB::table($this->getPerWeekViewName())->selectRaw('AVG(aggregate) AS aggregate, week AS period');

        if ($cb->isNotBlank('manufacturer')) {
            $query->where('manufacturer', $cb->get('manufacturer'));
        }

        if ($cb->isNotBlank('not_manufacturer')) {
            $query->where('manufacturer', '!=', $cb->get('not_manufacturer'));
        }

        if ($cb->isNotBlank('from')) {
            $from = Date::createFromFormat('Y-m-d', $cb->get('from', Date::now()->subYear()->format('Y-m-d')));
            $to = Date::createFromFormat('Y-m-d', $cb->get('to', Date::now()->format('Y-m-d')));

            $query->whereBetween('week', [
                sprintf('%d-%d', $from->isoWeekYear, $from->isoWeek),
                sprintf('%d-%d', $to->isoWeekYear, $to->isoWeek),
            ]);
        }

        $query->groupBy('week')
            ->orderBy('week');

        return $query->cursor();
    }

    public function getAllPeMonth(CriteriaBuilder $cb): LazyCollection
    {
        throw new NotImplementedException();
    }

    public function getAllPerYear(CriteriaBuilder $cb): LazyCollection
    {
        throw new NotImplementedException();
    }

    abstract protected function getPerDayViewName(): string;

    abstract protected function getPerWeekViewName(): string;

    abstract protected function getPerMonthViewName(): string;

    abstract protected function getPerYearViewName(): string;

    abstract protected function getAggregateName(): string;
}
