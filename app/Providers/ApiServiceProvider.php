<?php

namespace App\Providers;

use Dingo\Api\Exception\Handler;
use Illuminate\Database\QueryException;
use Illuminate\Support\ServiceProvider;
use Log;
use Str;
use Symfony\Component\HttpFoundation\Response;

class ApiServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $dingoHandler = resolve(Handler::class);

        $dingoHandler->register(function(QueryException $exception) {
            $logToken = Str::random();

            Log::error("$logToken -- QueryException: {$exception->getMessage()}", $exception->getBindings());

            return response()->json([
                'message' => "Something is wrong, please try again.",
                'dev_message' => "There is an error with the database query, the error is logged to the log file with the log token: $logToken.",
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    }
}
