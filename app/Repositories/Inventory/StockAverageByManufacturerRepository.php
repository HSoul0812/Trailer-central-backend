<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Support\CriteriaBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class StockAverageByManufacturerRepository implements StockAverageByManufacturerRepositoryInterface
{
    public function getAllManufacturers(): Collection
    {
        return DB::table('inventory_stock_average_per_week')
            ->select('manufacturer')
            ->distinct()
            ->get();
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string, [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerDay(CriteriaBuilder $cb): LazyCollection
    {
        $query = DB::table('inventory_stock_average_per_day')->selectRaw('SUM(stock) AS stock, day AS period');

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
        $query = DB::table('inventory_stock_average_per_week')->selectRaw('SUM(stock) AS stock, week AS period');

        if ($cb->isNotBlank('manufacturer')) {
            $query->where('manufacturer', $cb->get('manufacturer'));
        }

        if ($cb->isNotBlank('not_manufacturer')) {
            $query->where('manufacturer', '!=', $cb->get('not_manufacturer'));
        }

        if ($cb->isNotBlank('from')) {
            $from = $cb->get('from');
            $to = $cb->get('to', Date::now()->format('y-m-d'));

            $query->whereBetween('week', [
                Date::createFromFormat('y-m-d', $from)->format('Y-W'),
                Date::createFromFormat('y-m-d', $to)->format('Y-W'),
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
}
