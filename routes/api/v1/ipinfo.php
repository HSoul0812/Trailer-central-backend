<?php

declare(strict_types=1);

use App\Http\Controllers\v1\IpInfo\IpInfoController;

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', ['middleware' => 'human-only'], function ($api) {
    $api->group(['prefix' => '/ipinfo'], function ($api) {
        $api->get('/city', [IpInfoController::class, 'index']);
    });
});
