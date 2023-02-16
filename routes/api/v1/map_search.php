<?php

declare(strict_types=1);

use App\Http\Controllers\v1\MapSearch\MapSearchController;

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    $api->group(['prefix' => '/map_search', 'middleware' => ['human-only']], function ($api) {
        $api->get('/geocode', [MapSearchController::class, 'geocode']);
        $api->get('/autocomplete', [MapSearchController::class, 'autocomplete']);
        $api->get('/reverse', [MapSearchController::class, 'reverse']);
        $api->get('/test', function() {
            return [];
        });
    });
});
