<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\MapSearch;

use App\Services\MapSearch\HereMapSearchClient;
use App\Services\MapSearch\HereMapSearchService;
use App\Services\MapSearch\MapSearchServiceInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Tests\Common\TestCase;

class HereMapSearchServiceTest extends TestCase
{
    public function testGeocode()
    {
        $geocodeJson = '{
          "items": [
            {
              "title": "William S Canning Blvd, Fall River, MA 02721, United States",
              "id": "here:af:streetsection:KP7caZtnAJGkaRd6YMUrLA",
              "resultType": "street",
              "address": {
                "label": "William S Canning Blvd, Fall River, MA 02721, United States",
                "countryCode": "USA",
                "countryName": "United States",
                "stateCode": "MA",
                "state": "Massachusetts",
                "county": "Bristol",
                "city": "Fall River",
                "district": "Maplewood",
                "street": "William S Canning Blvd",
                "postalCode": "02721"
              },
              "position": {
                "lat": 41.67052,
                "lng": -71.16053
              },
              "mapView": {
                "west": -71.16259,
                "south": 41.66445,
                "east": -71.15522,
                "north": 41.67777
              },
              "scoring": {
                "queryScore": 0.97,
                "fieldScore": {
                  "state": 1.0,
                  "city": 1.0,
                  "streets": [
                    1.0
                  ],
                  "postalCode": 0.6
                }
              }
            }
          ]
        }';
        $historyContainer = [];
        $this->mockHttpClient($geocodeJson, $historyContainer);
        $service = $this->getConcreteService();
        $service->geocode('test');

        $request = $historyContainer[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('geocode.search.hereapi.com', $request->getUri()->getHost());
        $this->assertEquals('q=test&in=countryCode%3ACAN%2CUSA', $request->getUri()->getQuery());
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testAutocomplete()
    {
        $autocompleteJson = '{
          "items": [
            {
              "title": "United States, MA, Fall River, William S Canning Blvd",
              "id": "here:af:streetsection:KP7caZtnAJGkaRd6YMUrLA",
              "language": "en",
              "resultType": "street",
              "address": {
                "label": "William S Canning Blvd, Fall River, MA 02721, United States",
                "countryCode": "USA",
                "countryName": "United States",
                "stateCode": "MA",
                "state": "Massachusetts",
                "county": "Bristol",
                "city": "Fall River",
                "district": "Maplewood",
                "street": "William S Canning Blvd",
                "postalCode": "02721"
              },
              "highlights": {
                "title": [
                  {
                    "start": 15,
                    "end": 17
                  },
                  {
                    "start": 19,
                    "end": 29
                  },
                  {
                    "start": 31,
                    "end": 53
                  }
                ],
                "address": {
                  "label": [
                    {
                      "start": 0,
                      "end": 22
                    },
                    {
                      "start": 24,
                      "end": 34
                    },
                    {
                      "start": 36,
                      "end": 38
                    }
                  ],
                  "stateCode": [
                    {
                      "start": 0,
                      "end": 2
                    }
                  ],
                  "city": [
                    {
                      "start": 0,
                      "end": 10
                    }
                  ],
                  "street": [
                    {
                      "start": 0,
                      "end": 22
                    }
                  ]
                }
              }
            }
          ]
        }';

        $historyContainer = [];
        $this->mockHttpClient($autocompleteJson, $historyContainer);
        $service = $this->getConcreteService();
        $service->autocomplete('test');
        $this->assertCount(1, $historyContainer);

        $request = $historyContainer[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('autocomplete.search.hereapi.com', $request->getUri()->getHost());
        $this->assertEquals('q=test&in=countryCode%3ACAN%2CUSA', $request->getUri()->getQuery());
    }

    public function testReverse()
    {
        $reverseJson = '{
          "items": [
            {
              "title": "William S Canning Blvd, Fall River, MA 02721, United States",
              "id": "here:af:streetsection:KP7caZtnAJGkaRd6YMUrLA",
              "resultType": "street",
              "address": {
                "label": "William S Canning Blvd, Fall River, MA 02721, United States",
                "countryCode": "USA",
                "countryName": "United States",
                "stateCode": "MA",
                "state": "Massachusetts",
                "county": "Bristol",
                "city": "Fall River",
                "district": "Maplewood",
                "street": "William S Canning Blvd",
                "postalCode": "02721"
              },
              "position": {
                "lat": 41.67052,
                "lng": -71.16053
              },
              "distance": 0,
              "mapView": {
                "west": -71.16259,
                "south": 41.66445,
                "east": -71.15522,
                "north": 41.67777
              }
            }
          ]
        }';
        $historyContainer = [];
        $this->mockHttpClient($reverseJson, $historyContainer);
        $service = $this->getConcreteService();
        $service->reverse(34.52, 60.52);
        $this->assertCount(1, $historyContainer);
        $request = $historyContainer[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('revgeocode.search.hereapi.com', $request->getUri()->getHost());
        $this->assertEquals('at=34.52%2C60.52&lang=en-US', $request->getUri()->getQuery());
    }

    private function mockHttpClient(string $mockData, array &$historyContainer)
    {
        $history = Middleware::history($historyContainer);
        $mock = new MockHandler([
            new Response(200, [], $mockData),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        app()->bind(HereMapSearchClient::class, function ($app) use ($stack) {
            return new HereMapSearchClient(['handler' => $stack]);
        });
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getConcreteService()
    {
        app()->bind(MapSearchServiceInterface::class, HereMapSearchService::class);

        return app()->make(MapSearchServiceInterface::class);
    }
}
