<?php

namespace App\Services\Integrations\TrailerCentral\Api\Users;

use App\DTOs\User\TcApiResponseUser;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    private function handleHttpRequest(string $method, string $url, array $options): array
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::info('Exception was thrown while calling TrailerCentral API.');
            \Log::info($e->getCode() . ': ' . $e->getMessage());

            throw new HttpException(422, $e->getMessage());
        }
    }
}
