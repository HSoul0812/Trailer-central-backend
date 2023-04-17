<?php

namespace App\Http\Middleware;

use App\Domains\Http\Response\Header;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyHeaderQueue
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->add(
            headers: resolve(Header::class)->getQueue()
        );

        return $response;
    }
}
