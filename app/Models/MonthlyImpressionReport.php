<?php

namespace App\Models;

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
    ];
}
