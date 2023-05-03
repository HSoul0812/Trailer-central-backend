<?php

namespace App\Services\MapSearch;

use App\DTOs\MapSearch\GoogleAutocompleteResponse;
use App\DTOs\MapSearch\GoogleGeocodeResponse;
use App\Transformers\MapSearch\GoogleAutocompleteResponseTransformer;
use App\Transformers\MapSearch\GoogleGeocodeResponseTransformer;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\Pure;
use League\Fractal\TransformerAbstract;
use Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GoogleMapSearchService implements MapSearchServiceInterface
{
    private const GEOCODE_API_URL = 'https://maps.googleapis.com/maps/api/geocode/json';
    private const AUTOCOMPLETE_API_URL = 'https://maps.googleapis.com/maps/api/place/autocomplete/json';
    private array $transformers;

    public function __construct(private GoogleMapSearchClient $httpClient)
    {
        $this->transformers = [
            GoogleGeocodeResponse::class => GoogleGeocodeResponseTransformer::class,
            GoogleAutocompleteResponse::class => GoogleAutocompleteResponseTransformer::class,
        ];
    }

    public static function register()
    {
        app()->bind(MapSearchServiceInterface::class, self::class);
        app()->bind(GoogleMapSearchClient::class, function ($app) {
            return GoogleMapSearchClient::newClient();
        });
    }

    public function autocomplete(string $searchText): GoogleAutocompleteResponse
    {
        $url = self::AUTOCOMPLETE_API_URL;

        return GoogleAutocompleteResponse::fromData(
            $this->handleHttpRequest('GET', $url, [
                'query' => [
                    'input' => $searchText,
                    'types' => '(regions)',
                    'components' => 'country:US|country:CA',
                ],
            ])
        );
    }

    public function geocode(string $address): GoogleGeocodeResponse
    {
        $url = self::GEOCODE_API_URL;

        return GoogleGeocodeResponse::fromData(
            $this->handleHttpRequest('GET', $url, [
                'query' => [
                    'address' => $address,
                    'components' => 'country:US|country:CA',
                ],
            ])
        );
    }

    public function reverse(float $lat, float $lng): GoogleGeocodeResponse
    {
        $url = self::GEOCODE_API_URL;

        return GoogleGeocodeResponse::fromData(
            $this->handleHttpRequest('GET', $url, [
                'query' => [
                    'latlng' => "$lat,$lng",
                ],
            ])
        );
    }

    #[Pure]
    public function getTransformer(string $class): TransformerAbstract
    {
        return new $this->transformers[$class]();
    }

    private function handleHttpRequest(string $method, string $url, array $options): array
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::info('Exception was thrown while calling here API.');
            Log::info($e->getCode() . ': ' . $e->getMessage());

            throw new HttpException(500, $e->getMessage());
        }
    }
}
