<?php

declare(strict_types=1);

namespace App\Services\MapSearchService;

use App\DTOs\MapSearch\TomTomApiResponse;
use App\Transformers\MapSearch\TomTomApiResponseTransformer;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use League\Fractal\TransformerAbstract;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TomTomMapSearchService implements MapSearchServiceInterface
{
    private const AUTOCOMPLETE_API_URL = 'https://api.tomtom.com/search/2/geocode/';
    private const REVERSE_API_URL = 'https://api.tomtom.com/search/2/reverseGeocode/';

    public function __construct(private TomTomMapSearchClient $httpClient)
    {
    }

    public static function register()
    {
        app()->bind(MapSearchServiceInterface::class, self::class);
        app()->bind(TomTomMapSearchClient::class, function ($app) {
            return TomTomMapSearchClient::newClient();
        });
    }

    public function autocomplete(string $searchText): TomTomApiResponse
    {
        $url = self::AUTOCOMPLETE_API_URL . $searchText . ".json";
        return TomTomApiResponse::fromData(
            $this->handleHttpRequest('GET', $url, [
                'query' => [
                    'countrySet' => 'US,CA',
                    'typeahead' => 'true'
                ]
            ])
        );
    }

    public function geocode(string $address): TomTomApiResponse
    {
        return $this->autocomplete($address);
    }

    public function reverse(float $lat, float $lng): TomTomApiResponse
    {
        $url = self::REVERSE_API_URL . "$lat,$lng" . ".json";

        return TomTomApiResponse::fromData(
            $this->handleHttpRequest('GET', $url, [])
        );
    }

    /**
     * @return TomTomApiResponseTransformer
     */
    #[Pure]
    public function getTransformer(): TomTomApiResponseTransformer
    {
        return new TomTomApiResponseTransformer();
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $options
     *
     * @return array
     */
    #[ArrayShape([
        'items' => [[
            'title'   => 'string',
            'address' => [
                'label'       => 'string',
                'countryCode' => 'string',
                'countryName' => 'string',
                'stateCode'   => 'string',
                'state'       => 'string',
                'county'      => 'string',
                'city'        => 'string',
                'district'    => 'string',
                'street'      => 'string',
                'postalCode'  => 'string',
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
            \Log::info('Exception was thrown while calling here API.');
            \Log::info($e->getCode() . ': ' . $e->getMessage());

            throw new HttpException(500, $e->getMessage());
        }
    }
}
