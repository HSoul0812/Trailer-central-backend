<?php

namespace App\Http\Middleware;

use Closure;
use Dingo\Api\Http\Response as DingoResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Response as IlluminateResponse;

class GzipResponse
{
    const COMPRESSION_LEVEL = 9;

    public function handle(Request $request, Closure $next): IlluminateResponse
    {
        $response = $next($request);

        if ($response instanceof DingoResponse) {
            return $this->compressDingoResponse($response);
        }

        if ($response instanceof IlluminateResponse) {
            return $this->compressIlluminateResponse($response);
        }

        return $next($response);
    }

    private function compressDingoResponse(DingoResponse $response): Response
    {
        return $this->encodedResponse($response->morph()->content());
    }

    private function compressIlluminateResponse(IlluminateResponse $response): Response
    {
        return $this->encodedResponse($response->content());
    }

    private function encodedResponse(string $content): Response
    {
        $data = gzencode($content, self::COMPRESSION_LEVEL);

        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}
