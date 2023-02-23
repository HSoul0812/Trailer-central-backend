<?php

namespace App\Services\Dealers;

use Closure;
use Http;
use Illuminate\Http\Client\Response;
use Log;
use Throwable;

class DealerService implements DealerServiceInterface
{
    const ENDPOINT_USERS_BY_NAME = '/users-by-name';

    public function listByName(string $name): ?array
    {
        return $this->handleHttpRequest('GET', self::ENDPOINT_USERS_BY_NAME, [
            'query' => [
                'name' => $name,
            ],
        ])?->json('data');
    }

    private function handleHttpRequest(string $method, string $url, array $options = []): ?Response
    {
        try {
            return Http::tcApi()->send($method, $url, $options);
        } catch (Throwable $e) {
            Log::info('Exception was thrown while calling TrailerCentral API.');
            Log::info($e->getCode() . ': ' . $e->getMessage());
        }

        return null;
    }
}
