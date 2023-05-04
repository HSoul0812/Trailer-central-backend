<?php

namespace App\Services\Integrations\TrailerCentral\Api\Users;

use App\DTOs\User\TcApiResponseUser;
use App\DTOs\User\TcApiResponseUserLocation;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Log;

class UsersService implements UsersServiceInterface
{
    private string $endpointUrl;

    public function __construct(
        private GuzzleHttpClient $httpClient
    ) {
        $this->endpointUrl = config('services.trailercentral.api') . 'users';
    }

    public function create(array $attributes): TcApiResponseUser
    {
        $responseContent = $this->handleHttpRequest('POST', $this->endpointUrl, [
            'json' => $attributes,
        ]);

        return TcApiResponseUser::fromData($responseContent['data']);
    }

    public function get(string $email): TcApiResponseUser
    {
        $responseContent = $this->handleHttpRequest('GET', $this->endpointUrl, [
            'query' => [
                'email' => $email,
            ],
        ]);

        return TcApiResponseUser::fromData($responseContent);
    }

    public function createLocation(array $location): TcApiResponseUserLocation
    {
        $responseContent = $this->handleHttpRequest(
            'PUT',
            config('services.trailercentral.api') . 'user/dealer-location',
            [
                'json' => $location,
            ]
        );

        return TcApiResponseUserLocation::fromData($responseContent);
    }

    public function updateLocation(int $locationId, array $location): TcApiResponseUserLocation
    {
        $responseContent = $this->handleHttpRequest(
            'POST',
            config('services.trailercentral.api') . 'user' . "/dealer-location/$locationId",
            [
                'json' => $location,
            ]
        );

        return TcApiResponseUserLocation::fromData($responseContent);
    }

    private function handleHttpRequest(string $method, string $url, array $options): array
    {
        $accessToken = request()->header('access-token');
        $options['headers'] = [
            'access-token' => $accessToken,
        ];

        try {
            $response = $this->httpClient->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::info('Exception was thrown while calling TrailerCentral API.');
            Log::info($e->getCode() . ': ' . $e->getMessage());

            throw $e;
        }
    }
}
