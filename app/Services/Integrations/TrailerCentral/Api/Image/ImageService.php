<?php

namespace App\Services\Integrations\TrailerCentral\Api\Image;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;

class ImageService implements ImageServiceInterface
{
    private string $endpointUrl;

    public function __construct(
        private GuzzleHttpClient $httpClient
    ) {
        $this->endpointUrl = config('services.trailercentral.api') . 'images/local';
    }

    /**
     * @throws GuzzleException
     */
    public function uploadImage(int $dealerId, string $accessToken, string $imagePath)
    {
        try {
            $response = $this->httpClient->post($this->endpointUrl, [
                'headers' => [
                    'access-token' => $accessToken
                ],
                'multipart' => [
                    [
                        'name' => 'dealer_id',
                        'contents' => $dealerId
                    ],
                    [
                        'name' => 'file',
                        'contents' => Utils::tryFopen($imagePath, 'r')
                    ]
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::info('Exception was thrown while calling TrailerCentral API.');
            \Log::info($e->getCode() . ': ' . $e->getMessage());

            throw $e;
        }

    }
}
