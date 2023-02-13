<?php

namespace App\Services\Integrations\TrailerCentral\Api\Users;

use App\DTOs\User\TcApiResponseUser;
use App\DTOs\User\TcApiResponseUserLocation;
use App\Repositories\Integrations\TrailerCentral\AuthTokenRepositoryInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;

class UsersService implements UsersServiceInterface
{
    private string $endpointUrl;

    public function __construct(
        private GuzzleHttpClient $httpClient,
        private AuthTokenRepositoryInterface $authTokenRepository
    ) {
        $this->endpointUrl = config('services.trailercentral.api') . 'users';
    }

    public function create(array $attributes): TcApiResponseUser
    {
        $responseContent = $this->handleHttpRequest('POST', $this->endpointUrl, [
            'json' => $attributes
        ]);
        return TcApiResponseUser::fromData($responseContent['data']);
    }

    public function get(string $email): TcApiResponseUser
    {
        $responseContent = $this->handleHttpRequest('GET', $this->endpointUrl, [
            'query' => [
                'email' => $email
            ]
        ]);
        return TcApiResponseUser::fromData($responseContent);
    }

    public function createLocation(array $location): TcApiResponseUserLocation {
        if(!$accessToken = request()->header('access-token')) {
            $authToken = $this->authTokenRepository->get(['user_id' => $location['dealer_id']]);
            $accessToken = $authToken->access_token;
        }

        $responseContent = $this->handleHttpRequest(
            'PUT',
            config('services.trailercentral.api') . 'user' . "/dealer-location",
            [
                'json' => $location,
                'headers' => [
                    'access-token' => $accessToken
                ]
            ]
        );
        return TcApiResponseUserLocation::fromData($responseContent['data']);
    }

    public function updateLocation(int $locationId, array $location): TcApiResponseUserLocation {
        if(!$accessToken = request()->header('access-token')) {
            $authToken = $this->authTokenRepository->get(['user_id' => $location['dealer_id']]);
            $accessToken = $authToken->access_token;
        }

        $responseContent = $this->handleHttpRequest(
            'POST',
            config('services.trailercentral.api') . 'user' . "/dealer-location/$locationId",
            [
                'json' => $location,
                'headers' => [
                    'access-token' => $accessToken
                ]
            ]
        );
        return TcApiResponseUserLocation::fromData($responseContent['data']);
    }

    private function handleHttpRequest(string $method, string $url, array $options): array
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::info('Exception was thrown while calling TrailerCentral API.');
            \Log::info($e->getCode() . ': ' . $e->getMessage());

            throw $e;
        }
    }
}
