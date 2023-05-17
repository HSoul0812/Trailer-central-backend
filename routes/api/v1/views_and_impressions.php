<?php

declare(strict_types=1);

/** @var Router $api */

use App\Http\Controllers\v1\ViewsAndImpressions\DownloadTTAndAffiliateMonthlyCountingController;
use App\Http\Controllers\v1\ViewsAndImpressions\TTAndAffiliateController;
use Dingo\Api\Routing\Router;

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function (Router $api) {
    $api->group(['prefix' => '/views-and-impressions', 'middleware' => ['human-only', 'gzip']], function (Router $api) {
        $api->get('/tt-and-affiliate', [TTAndAffiliateController::class, 'index']);
        $api->get('/tt-and-affiliate/download-zip', [DownloadTTAndAffiliateMonthlyCountingController::class, 'index']);
    });
});
