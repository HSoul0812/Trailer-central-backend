<?php

declare(strict_types=1);

namespace App\Services\MapSearch;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

class TomTomMapSearchClient extends Client
{
    public static function newClient(): self
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withUri(Uri::withQueryValues(
                $request->getUri(),
                [
                    'key' => config('services.tomtom.key'),
                    'language' => 'en-US',
                ]
            ));
        }));

        return new self(['handler' => $stack]);
    }
}
