<?php

require_once __DIR__.'/../vendor/autoload.php';

use Dingo\Api\Http\Request;

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

 $app->withFacades();

 $app->withEloquent();
 
 $app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');

 $app->configure('swagger-lume');
 $app->configure('scout');
 $app->configure('session');
 $app->configure('database');
/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(Request::class, function () {
     return Request::capture();
});

$app->alias(Request::class, 'request');

$app->singleton('filesystem', function ($app) {
    return $app->loadComponent(
        'filesystems',
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        'filesystem'
    );
});

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    App\Http\Middleware\AccessToken::class,
    'Illuminate\Session\Middleware\StartSession'
]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);
$app->register(\SwaggerLume\ServiceProvider::class);
$app->register(Laravel\Scout\ScoutServiceProvider::class);
$app->register(Illuminate\Session\SessionServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);


$app->register(
    Dingo\Api\Provider\LumenServiceProvider::class,
    $app->bind('App\Repositories\Parts\PartRepositoryInterface', 'App\Repositories\Parts\PartRepository'),
    $app->bind('App\Repositories\Parts\BrandRepositoryInterface', 'App\Repositories\Parts\BrandRepository'),
    $app->bind('App\Repositories\Parts\CategoryRepositoryInterface', 'App\Repositories\Parts\CategoryRepository'),
    $app->bind('App\Repositories\Parts\ManufacturerRepositoryInterface', 'App\Repositories\Parts\ManufacturerRepository'),
    $app->bind('App\Repositories\Parts\TypeRepositoryInterface', 'App\Repositories\Parts\TypeRepository'),
    $app->bind('App\Repositories\Website\Parts\FilterRepositoryInterface', 'App\Repositories\Website\Parts\FilterRepository'),
    $app->bind(Illuminate\Session\SessionManager::class, function ($app) {    
        return $app->make('session');
    })
);


/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

$app->configure('filesystems');

return $app;
