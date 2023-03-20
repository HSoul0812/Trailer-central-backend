<?php

namespace App\Http\Middleware;

use Closure;
use Dingo\Api\Http\Response as DingoResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Str;

class GzipResponse
{
    const HEADER_ACCEPT_ENCODING = 'Accept-Encoding';

    const HEADER_CONTENT_ENCODING = 'Content-Encoding';

    const COMPRESSION_LEVEL = 9;

    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        // No need to compress if the request can't accept it
        if (!$this->shouldCompress($request)) {
            return $response;
        }

        if (gettype($response) === 'string') {
            return $response;
        }

        return match (get_class($response)) {
            DingoResponse::class => $this->compressDingoResponse($response),
            JsonResponse::class => $this->compressIlluminateResponse($response),
            default => $response,
        };
    }

    private function compressDingoResponse(DingoResponse $response): Response
    {
        return $this->encodedResponse($response->morph()->content());
    }

    private function compressIlluminateResponse(JsonResponse $response): Response
    {
        return $this->encodedResponse($response->content());
    }

    private function encodedResponse(string $content): Response
    {
        $data = gzencode($content, self::COMPRESSION_LEVEL);

        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($data),
            self::HEADER_CONTENT_ENCODING => 'gzip'
        ]);
    }

    private function shouldCompress(Request $request): bool
    {
        $acceptEncoding = $request->header(self::HEADER_ACCEPT_ENCODING, '');

        return Str::of($acceptEncoding)->contains('gzip');
    }
}
