<?php

declare(strict_types=1);

namespace App\Services\MapSearch;

use App\DTOs\MapSearch\HereResponse;
use App\Transformers\MapSearch\HereResponseTransformer;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use League\Fractal\TransformerAbstract;
use Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HereMapSearchService implements MapSearchServiceInterface
{
    private const AUTOCOMPLETE_API_URL = 'https://autocomplete.search.hereapi.com/v1/autocomplete';
    private const GEOCODE_API_URL = 'https://geocode.search.hereapi.com/v1/geocode';
    private const REVERSE_API_URL = 'https://revgeocode.search.hereapi.com/v1/revgeocode';

    public function __construct(private HereMapSearchClient $httpClient)
    {
    }

    public static function register()
    {
        app()->bind(MapSearchServiceInterface::class, self::class);
        app()->bind(HereMapSearchClient::class, function ($app) {
            return HereMapSearchClient::newClient();
        });
    }

    public function autocomplete(string $searchText): HereResponse
    {
        $queryData = ['q' => $searchText, 'in' => 'countryCode:CAN,USA'];

        return HereResponse::fromData(
            $this->handleHttpRequest('GET', self::AUTOCOMPLETE_API_URL, ['query' => $queryData])
        );
    }

    public function geocode(string $address): HereResponse
    {
        $queryData = ['q' => $address, 'in' => 'countryCode:CAN,USA'];

        return HereResponse::fromData(
            $this->handleHttpRequest('GET', self::GEOCODE_API_URL, ['query' => $queryData])
        );
    }

    public function reverse(float $lat, float $lng): HereResponse
    {
        $queryData = ['at' => "$lat,$lng", 'lang' => 'en-US'];

        return HereResponse::fromData(
            $this->handleHttpRequest('GET', self::REVERSE_API_URL, ['query' => $queryData])
        );
    }

    #[Pure]
    public function getTransformer(string $class): TransformerAbstract
    {
        return new HereResponseTransformer();
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
