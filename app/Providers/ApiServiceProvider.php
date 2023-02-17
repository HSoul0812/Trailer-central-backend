<?php

namespace App\Providers;

use Dingo\Api\Exception\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class ApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {

    }

    public function register(): void
    {
        $this->registerDingoHandler();
    }

    private function registerDingoHandler(): void
    {
        /*
         * We need to customize the response for the AuthenticationException exception
         * If we don't do this, Dingo will return it with status code 500, which is incorrect
         *
         * Ref: https://github.com/dingo/api/wiki/Errors-And-Error-Responses#custom-exception-responses
         */
        app(Handler::class)->register(function (AuthenticationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status_code' => Response::HTTP_UNAUTHORIZED,
            ], Response::HTTP_UNAUTHORIZED);
        });

        app(Handler::class)->register(function (UnauthorizedException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status_code' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        });
    }
}
