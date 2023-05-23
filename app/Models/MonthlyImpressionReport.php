<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyImpressionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'dealer_id',
        'inventory_id',
        'inventory_title',
        'inventory_type',
        'inventory_category',
        'plp_total_count',
        'pdp_total_count',
        'tt_dealer_page_total_count',
        'site',
    ];

    public function scopeSite(Builder $query, string $site): void
    {
        $query->where('site', $site);
    }

    public function scopeYear(Builder $query, int $year): void
    {
        $query->where('year', $year);
    }

    public function scopeMonth(Builder $query, int $month): void
    {
        $query->where('month', $month);
    }

    public function scopeDealerId(Builder $query, int $dealerId): void
    {
        $query->where('dealer_id', $dealerId);
    }

    public function scopeYearMonthDealerId(Builder $query, int $year, int $month, int $dealerId): void
    {
        $query
            ->year($year)
            ->month($month)
            ->dealerId($dealerId);
    }
}
