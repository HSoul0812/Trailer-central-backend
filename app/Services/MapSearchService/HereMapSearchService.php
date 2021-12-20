<?php

declare(strict_types=1);

namespace App\Services\MapSearchService;

use App\DTOs\MapSearch\HereApiResponse;
use App\Transformers\MapSearch\HereApiResponseTransformer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use League\Fractal\TransformerAbstract;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HereMapSearchService implements MapSearchServiceInterface
{
    private const AUTOCOMPLETE_API_URL = 'https://autocomplete.search.hereapi.com/v1/autocomplete';
    private const GEOCODE_API_URL = 'https://geocode.search.hereapi.com/v1/geocode';
    private const REVERSE_API_URL = 'https://revgeocode.search.hereapi.com/v1/revgeocode';

    public function __construct(private HereMapSearchClient $httpClient) {}

    public static function register()
    {
        app()->bind(MapSearchServiceInterface::class, self::class);
        app()->bind(HereMapSearchClient::class, function ($app) {
            return HereMapSearchClient::newClient();
        });
    }

    /**
     * @param string $searchText
     * @return HereApiResponse
     */
    public function autocomplete(string $searchText): HereApiResponse
    {
        $queryData = ['q' => $searchText, 'in' => 'countryCode:CAN,USA'];

        return HereApiResponse::fromData(
            $this->handleHttpRequest('GET', self::AUTOCOMPLETE_API_URL, ['query' => $queryData])
        );
    }

    /**
     * @param string $address
     * @return HereApiResponse
     */
    public function geocode(string $address): HereApiResponse
    {
        $queryData = ['q' => $address, 'in' => 'countryCode:CAN,USA'];

        return HereApiResponse::fromData(
            $this->handleHttpRequest('GET', self::GEOCODE_API_URL, ['query' => $queryData])
        );
    }

    /**
     * @param float $lat
     * @param float $lng
     * @return HereApiResponse
     */
    public function reverse(float $lat, float $lng): HereApiResponse
    {
        $queryData = ['at' => "$lat,$lng", 'lang' => 'en-US'];

        return HereApiResponse::fromData(
            $this->handleHttpRequest('GET', self::REVERSE_API_URL, ['query' => $queryData])
        );
    }

    /**
     * @return TransformerAbstract
     */
    #[Pure]
    public function getTransformer(): TransformerAbstract
    {
        return new HereApiResponseTransformer();
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return array
     */
    #[ArrayShape([
        'items' => [[
            'title' => "string",
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
                'postalCode' => 'string'
            ],
            'position' => [
                'lat' => 'float',
                'lng' => 'float'
            ]
        ]]
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
