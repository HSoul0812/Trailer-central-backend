<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyImpressionCounting extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'dealer_id',
        'impressions_count',
        'views_count',
        'zip_file_path',
    ];

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
