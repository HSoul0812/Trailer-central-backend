<?php

namespace App\Services\Integrations\TrailerCentral\Api\Users;

use App\DTOs\User\TcApiResponseUser;
use App\DTOs\User\TcApiResponseUserLocation;
use App\Repositories\Integrations\TrailerCentral\AuthTokenRepositoryInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Log;
use Str;

class UsersService implements UsersServiceInterface
{
    private string $usersUrl;
    private string $userLocationUrl;
    private string $integrationToken;

    public function __construct(
        private GuzzleHttpClient $httpClient,
        private AuthTokenRepositoryInterface $authTokenRepository
    ) {
        $tcApiPath = config('services.trailercentral.api');
        $this->usersUrl = $tcApiPath . 'users';
        $this->userLocationUrl = $tcApiPath . 'user/dealer-location';
        $this->integrationToken = config('services.trailercentral.integration_access_token');
    }

    public function create(array $attributes): TcApiResponseUser
    {
        $responseContent = $this->handleHttpRequest('POST', $this->usersUrl, [
            'json' => $attributes,
            'headers' => [
                'access-token' => $this->integrationToken
            ]
        ]);
        return TcApiResponseUser::fromData($responseContent['data']);
    }

    public function get(string $email): TcApiResponseUser
    {
        $responseContent = $this->handleHttpRequest('GET', $this->usersUrl, [
            'query' => [
                'email' => $email
            ]
        ]);
        return TcApiResponseUser::fromData($responseContent);
    }

    public function getLocations(int $userId): array
    {
        if(!$accessToken = request()->header('access-token')) {
            $authToken = $this->authTokenRepository->get(['user_id' => $userId]);
            $accessToken = $authToken->access_token;
        }

        $responseContent = $this->handleHttpRequest(
            'GET',
            $this->userLocationUrl,
            [
                'json' => [],
                'headers' => [
                    'access-token' => $accessToken
                ]
            ]
        );

        $locations = [];
        foreach($responseContent['data'] as $location) {
            $locations[] = TcApiResponseUserLocation::fromData($location);
        }
        return $locations;
    }

    public function createLocation(array $location): TcApiResponseUserLocation {
        $authToken = $this->authTokenRepository->get(['user_id' => $location['dealer_id']]);
        $accessToken = $authToken->access_token;

        if (!array_key_exists('name', $location) || empty($location['name'])) {
            $location['name'] = collect([
                data_get($location, 'contact'),
                uniqid(),
            ])->filter()->values()->implode(' - ');
        }

        $responseContent = $this->handleHttpRequest(
            'PUT',
            $this->userLocationUrl,
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
            $this->userLocationUrl . "/$locationId",
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
            Log::info('Exception was thrown while calling TrailerCentral API.');
            Log::info($e->getCode() . ': ' . $e->getMessage());

            throw $e;
        }
    }
}
