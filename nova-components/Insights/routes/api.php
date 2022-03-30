<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Card API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your card. These routes
| are loaded by the ServiceProvider of your card. You're free to add
| as many additional routes to this file as your card may require.
|
*/

Route::post('/stock-average-by-manufacturer-insights', [\App\Nova\Dashboards\Inventory\StockAverageByManufacturerInsights::class, 'data']);
Route::post('/price-average-by-manufacturer-insights', [\App\Nova\Dashboards\Inventory\PriceAverageByManufacturerInsights::class, 'data']);
Route::post('/leads-average-by-manufacturer-insights', [\App\Nova\Dashboards\Leads\LeadsAverageByManufacturerInsights::class, 'data']);
