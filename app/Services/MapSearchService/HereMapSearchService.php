<?php

declare(strict_types=1);

namespace App\Services\MapSearchService;

use App\Transformers\MapSearch\HereMapSearchTransformer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use League\Fractal\TransformerAbstract;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HereMapSearchService implements MapSearchServiceInterface
{
    private const AUTOCOMPLETE_API_URL = 'https://autocomplete.search.hereapi.com/v1/autocomplete';
    private const GEOCODE_API_URL = 'https://geocode.search.hereapi.com/v1/geocode';
    private const REVERSE_API_URL = 'https://revgeocode.search.hereapi.com/v1/revgeocode';

    private Client $httpClient;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct()
    {
        $this->httpClient = app()->make('HereMapSearchService.client');
    }

    public static function register()
    {
        app()->bind(MapSearchServiceInterface::class, self::class);
        app()->bind('HereMapSearchService.client', function ($app) {
            $stack = new HandlerStack();
            $stack->setHandler(new CurlHandler());
            $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
                return $request->withUri(Uri::withQueryValue(
                    $request->getUri(),
                    'apikey',
                    config('services.here.key')
                ));
            }));

            return new Client(['handler' => $stack]);
        });
    }

    /**
     * @throws HttpException
     */
    public function autocomplete(string $searchText): object
    {
        $queryData = ['q' => $searchText, 'in' => 'countryCode:CAN,USA'];

        return $this->handleHttpRequest('GET', self::AUTOCOMPLETE_API_URL, ['query' => $queryData]);
    }

    /**
     * @throws HttpException
     *
     * @return mixed
     */
    public function geocode(string $address): object
    {
        $queryData = ['q' => $address, 'in' => 'countryCode:CAN,USA'];

        return $this->handleHttpRequest('GET', self::GEOCODE_API_URL, ['query' => $queryData]);
    }

    /**
     * @param string $lat
     * @param string $lng
     *
     * @throws HttpException
     *
     * @return mixed
     */
    public function reverse(float $lat, float $lng): object
    {
        $queryData = ['at' => "$lat,$lng", 'lang' => 'en-US'];

        return $this->handleHttpRequest('GET', self::REVERSE_API_URL, ['query' => $queryData]);
    }

    public function getTransformer(): TransformerAbstract
    {
        return new HereMapSearchTransformer();
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function handleHttpRequest(string $method, string $url, array $options)
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);

            return json_decode($response->getBody()->getContents());
        } catch (GuzzleException $e) {
            \Log::info('Exception was thrown while calling here API.');
            \Log::info($e->getCode() . ': ' . $e->getMessage());

            throw new HttpException(500, $e->getMessage());
        }
    }
}
