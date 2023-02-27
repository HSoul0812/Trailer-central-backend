<?php

namespace App\Services\Dealers;

use App\DTOs\Dealer\TcApiResponseDealer;
use Closure;
use Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Log;
use Throwable;

class DealerService implements DealerServiceInterface
{
    const ENDPOINT_USERS_BY_NAME = '/users-by-name';

    /**
     * @param string $name
     * @return Collection<int, TcApiResponseDealer>
     */
    public function listByName(string $name): Collection
    {
        $dealers = $this->handleHttpRequest('GET', self::ENDPOINT_USERS_BY_NAME, [
            'query' => [
                'name' => $name,
            ],
        ])?->collect('data');

        if ($dealers === null) {
            return collect([]);
        }

        return $dealers->map(
            fn(array $dealer) => TcApiResponseDealer::fromData($dealer)
        );
    }

    /**
     * Handle the HTTP request, helper method for this class
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return Response|null
     */
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
