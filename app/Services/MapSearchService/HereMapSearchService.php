<?php

declare(strict_types=1);

namespace App\Services\MapSearchService;

use App\Transformers\MapSearch\HereMapSearchTransformer;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use League\Fractal\TransformerAbstract;
use Psr\Http\Message\RequestInterface;

class HereMapSearchService implements MapSearchServiceInterface
{
    public const AUTOCOMPLETE_API_URL = 'https://autocomplete.search.hereapi.com/v1/autocomplete';
    public const GEOCODE_API_URL = 'https://geocode.search.hereapi.com/v1/geocode';
    public const REVERSE_API_URL = 'https://revgeocode.search.hereapi.com/v1/revgeocode';

    private Client $httpClient;

    public function __construct()
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withUri(Uri::withQueryValue(
                $request->getUri(),
                'apikey',
                config('services.here.key')
            ));
        }));
        $this->httpClient = new Client(['handler' => $stack]);
    }

    public function autocomplete(string $searchText): Object
    {
        $queryData = ['q' => $searchText, 'in' => 'countryCode:CAN,USA'];
        $response = $this->httpClient->request('GET', self::AUTOCOMPLETE_API_URL, ['query' => $queryData]);
        return json_decode($response->getBody()->getContents());
    }

    public function geocode(string $address)
    {
        $queryData = ['q' => $address, 'in' => 'countryCode:CAN,USA'];
        $response = $this->httpClient->request('GET', self::GEOCODE_API_URL, ['query' => $queryData]);
        return json_decode($response->getBody()->getContents());
    }

    public function reverse(String $lat, String $lng)
    {
        $queryData = ['at' => "$lat,$lng", 'lang' => 'en-US'];
        $response = $this->httpClient->request('GET', self::REVERSE_API_URL, ['query' => $queryData]);
        return json_decode($response->getBody()->getContents());
    }

    public function getTransformer(): TransformerAbstract
    {
        return new HereMapSearchTransformer();
    }
}
