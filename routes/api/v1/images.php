<?php

declare(strict_types=1);

use App\Http\Controllers\v1\Image\LocalImageController;

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    $api->group(['prefix' => '/images', 'middleware' => ['auth:api']], function ($api) {
        $api->post('/local', [LocalImageController::class, 'create'])->middleware(['human-only']);
    });
});
