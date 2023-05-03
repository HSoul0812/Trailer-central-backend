<?php

declare(strict_types=1);

namespace App\Services\MapSearch;

use App\DTOs\MapSearch\TomTomGeocodeResponse;
use App\DTOs\MapSearch\TomTomReverseGeocodeResponse;
use App\Transformers\MapSearch\TomTomGeocodeResponseTransformer;
use App\Transformers\MapSearch\TomTomReverseGeocodeResponseTransformer;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use League\Fractal\TransformerAbstract;
use Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TomTomMapSearchService implements MapSearchServiceInterface
{
    private const AUTOCOMPLETE_API_URL = 'https://api.tomtom.com/search/2/geocode/';
    private const REVERSE_API_URL = 'https://api.tomtom.com/search/2/reverseGeocode/';
    private array $transformers;

    public function __construct(private TomTomMapSearchClient $httpClient)
    {
        $this->transformers = [
            TomTomGeocodeResponse::class => TomTomGeocodeResponseTransformer::class,
            TomTomReverseGeocodeResponse::class => TomTomReverseGeocodeResponseTransformer::class,
        ];
    }

    public static function register()
    {
        app()->bind(MapSearchServiceInterface::class, self::class);
        app()->bind(TomTomMapSearchClient::class, function ($app) {
            return TomTomMapSearchClient::newClient();
        });
    }

    public function autocomplete(string $searchText): TomTomGeocodeResponse
    {
        $url = self::AUTOCOMPLETE_API_URL . $searchText . '.json';

        return TomTomGeocodeResponse::fromData(
            $this->handleHttpRequest('GET', $url, [
                'query' => [
                    'countrySet' => 'US,CA',
                    'typeahead' => 'true',
                ],
            ])
        );
    }

    public function geocode(string $address): TomTomGeocodeResponse
    {
        return $this->autocomplete($address);
    }

    public function reverse(float $lat, float $lng): TomTomReverseGeocodeResponse
    {
        $url = self::REVERSE_API_URL . "$lat,$lng" . '.json';

        return TomTomReverseGeocodeResponse::fromData(
            $this->handleHttpRequest('GET', $url, [])
        );
    }

    #[Pure]
    public function getTransformer(string $class): TransformerAbstract
    {
        return new $this->transformers[$class]();
    }

    #[ArrayShape([
        'items' => [[
            'title' => 'string',
            'address' => [
                'label' => 'string',
                'countryCode' => 'string',
                'countryName' => 'string',
                'stateCode' => 'string',
                'state' => 'string',
                'county' => 'string',
                'city' => 'string',
                'district' => 'string',
                'street' => 'string',
                'postalCode' => 'string',
            ],
            'position' => [
                'lat' => 'float',
                'lng' => 'float',
            ],
        ]],
    ])]
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
