<?php

namespace App\Providers;

use Dingo\Api\Exception\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

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
        $dingoHandler = app(Handler::class);

        // A simple Transformer method to transform exception and status code to JSON response
        $jsonResponse = function (Throwable $throwable, int $statusCode) {
            return response()->json([
                'message' => $throwable->getMessage(),
                'status_code' => $statusCode,
            ], $statusCode);
        };

        /*
         * We need to customize the response for the AuthenticationException exception
         * If we don't do this, Dingo will return it with status code 500, which is incorrect
         *
         * Ref: https://github.com/dingo/api/wiki/Errors-And-Error-Responses#custom-exception-responses
         */
        $dingoHandler->register(fn (AuthenticationException $e) => $jsonResponse($e, Response::HTTP_UNAUTHORIZED));
        $dingoHandler->register(fn (UnauthorizedException $e) => $jsonResponse($e, Response::HTTP_BAD_REQUEST));
        $dingoHandler->register(fn (TokenBlacklistedException $e) => $jsonResponse($e, Response::HTTP_FORBIDDEN));
        $dingoHandler->register(fn (JWTException $e) => $jsonResponse($e, Response::HTTP_BAD_REQUEST));
    }
}
