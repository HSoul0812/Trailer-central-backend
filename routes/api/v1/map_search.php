<?php
use App\Http\Controllers\v1\MapSearch\MapSearchController;

declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    $api->group(['prefix'=> '/map_search'], function ($api) {
        $api->get('/geocode', [MapSearchController::class, 'geocode']);
        $api->get('/autocomplete', [MapSearchController::class, 'autocomplete']);
        $api->get('/reverse', [MapSearchController::class, 'reverse']);
    });
});
