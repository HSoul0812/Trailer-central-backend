<?php

declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Glossary
    |--------------------------------------------------------------------------
    */

    $api->get('glossary', 'App\Http\Controllers\v1\Glossary\GlossaryController@index')->middleware(['gzip']);
});
