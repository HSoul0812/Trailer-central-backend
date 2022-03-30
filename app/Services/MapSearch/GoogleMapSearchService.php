<?php

namespace App\Services\MapSearch;

use App\DTOs\MapSearch\GoogleGeocodeResponse;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\Pure;
use League\Fractal\TransformerAbstract;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GoogleMapSearchService implements MapSearchServiceInterface
{
    private const AUTOCOMPLETE_API_URL = 'https://maps.googleapis.com/maps/api/geocode/json';
    private const REVERSE_API_URL = 'https://api.tomtom.com/search/2/reverseGeocode/';
    private array $transformers;
    public function __construct(private GoogleMapSearchClient $httpClient)
    {
        $this->transformers = [
            GoogleGeocodeResponse::class => GoogleGeocodeResponseTransformer::class,
        ];
    }

    public static function register()
    {
        app()->bind(MapSearchServiceInterface::class, self::class);
        app()->bind(GoogleMapSearchClient::class, function ($app) {
            return GoogleMapSearchClient::newClient();
        });
    }

    public function autocomplete(string $searchText): GoogleGeocodeResponse
    {
        $url = self::AUTOCOMPLETE_API_URL;
        return GoogleGeocodeResponse::fromData(
            $this->handleHttpRequest('GET', $url, [
                'query' => [
                    'address' => $searchText
                ]
            ])
        );
    }

    public function geocode(string $address): GoogleGeocodeResponse
    {
        return $this->autocomplete($address);
    }

//    public function reverse(float $lat, float $lng): TomTomReverseGeocodeResponse
//    {
//        $url = self::REVERSE_API_URL . "$lat,$lng" . ".json";
//
//        return TomTomReverseGeocodeResponse::fromData(
//            $this->handleHttpRequest('GET', $url, [])
//        );
//    }

    /**
     * @param string $class
     * @return TransformerAbstract
     */
    #[Pure]
    public function getTransformer(string $class): TransformerAbstract
    {
        return new $this->transformers[$class];
    }

    private function handleHttpRequest(string $method, string $url, array $options): array
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::info('Exception was thrown while calling here API.');
            \Log::info($e->getCode() . ': ' . $e->getMessage());

            throw new HttpException(500, $e->getMessage());
        }
    }
}
