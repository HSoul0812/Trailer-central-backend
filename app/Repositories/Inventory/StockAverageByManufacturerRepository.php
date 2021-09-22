<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Support\CriteriaBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class StockAverageByManufacturerRepository implements StockAverageByManufacturerRepositoryInterface
{
    /**
     * @param CriteriaBuilder $cb {manufacturer:string, [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerDay(CriteriaBuilder $cb): Collection
    {
        $query = DB::table('inventory_stock_average_per_day')->selectRaw('SUM(stock) stock, day');

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

        return $query->get();
    }

    /**
     * @param CriteriaBuilder $cb {manufacturer:string, [from]:string[y-m-d], [to]:string[y-m-d]}
     */
    public function getAllPerWeek(CriteriaBuilder $cb): Collection
    {
        $query = DB::table('inventory_stock_average_per_week')->selectRaw('SUM(stock) stock, week');

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

        return $query->get();
    }

    public function getAllPeMonth(CriteriaBuilder $cb): Collection
    {
        throw new NotImplementedException();
    }

    public function getAllPerYear(CriteriaBuilder $cb): Collection
    {
        throw new NotImplementedException();
    }
}
