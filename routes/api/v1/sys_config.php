<?php
declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API SysConfig
    |--------------------------------------------------------------------------
    */

    $api->get('sys_config', 'App\Http\Controllers\v1\SysConfig\SysConfigController@index')->middleware(['gzip']);
});
