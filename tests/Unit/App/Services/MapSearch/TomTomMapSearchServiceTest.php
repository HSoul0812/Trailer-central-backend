<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\MapSearch;

use App\Services\MapSearch\MapSearchServiceInterface;
use App\Services\MapSearch\TomTomMapSearchClient;
use App\Services\MapSearch\TomTomMapSearchService;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Tests\Common\TestCase;

class TomTomMapSearchServiceTest extends TestCase
{
    public function testGeocode()
    {
        $geocodeJson = '{
          "summary": {
            "query": "k9j 2t8",
            "queryType": "NON_NEAR",
            "queryTime": 15,
            "numResults": 1,
            "offset": 0,
            "totalResults": 1,
            "fuzzyLevel": 1
          },
          "results": [
            {
              "type": "Street",
              "id": "CA/STR/p0/169602",
              "score": 5.1230435371,
              "address": {
                "streetName": "Charlotte Street",
                "municipality": "Peterborough",
                "countrySubdivision": "ON",
                "countrySubdivisionName": "Ontario",
                "postalCode": "K9H, K9J",
                "extendedPostalCode": "K9J 0A2, K9J 0B2, K9J 2T6, K9J 2T7, K9J 2T8, K9J 2V1, K9J 2V2, K9J 2V3, K9J 2V4, K9J 2V5, K9J 2V6, K9J 2V7, K9J 2V8, K9J 2V9, K9J 2W1, K9J 2W2, K9J 2W3, K9J 2W4, K9J 2W5, K9J 2W6, K9J 2W7, K9J 2W8, K9J 2W9, K9J 2X2, K9J 2X3, K9J 2X4, K9J 2X5, K9J 7K6, K9J 7L4, K9J 8M6",
                "countryCode": "CA",
                "country": "Canada",
                "countryCodeISO3": "CAN",
                "freeformAddress": "Charlotte Street, Peterborough ON K9H, K9J",
                "localName": "Peterborough"
              },
              "position": {
                "lat": 44.30306,
                "lon": -78.32773
              },
              "viewport": {
                "topLeftPoint": {
                  "lat": 44.31154,
                  "lon": -78.34073
                },
                "btmRightPoint": {
                  "lat": 44.29355,
                  "lon": -78.31559
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
        $this->assertEquals('api.tomtom.com', $request->getUri()->getHost());
        $this->assertEquals('/search/2/geocode/test.json', $request->getUri()->getPath());
        $this->assertEquals('countrySet=US%2CCA&typeahead=true', $request->getUri()->getQuery());
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testAutocomplete()
    {
        $autocompleteJson = '{
          "summary": {
            "query": "k9j 2t8",
            "queryType": "NON_NEAR",
            "queryTime": 15,
            "numResults": 1,
            "offset": 0,
            "totalResults": 1,
            "fuzzyLevel": 1
          },
          "results": [
            {
              "type": "Street",
              "id": "CA/STR/p0/169602",
              "score": 5.1230435371,
              "address": {
                "streetName": "Charlotte Street",
                "municipality": "Peterborough",
                "countrySubdivision": "ON",
                "countrySubdivisionName": "Ontario",
                "postalCode": "K9H, K9J",
                "extendedPostalCode": "K9J 0A2, K9J 0B2, K9J 2T6, K9J 2T7, K9J 2T8, K9J 2V1, K9J 2V2, K9J 2V3, K9J 2V4, K9J 2V5, K9J 2V6, K9J 2V7, K9J 2V8, K9J 2V9, K9J 2W1, K9J 2W2, K9J 2W3, K9J 2W4, K9J 2W5, K9J 2W6, K9J 2W7, K9J 2W8, K9J 2W9, K9J 2X2, K9J 2X3, K9J 2X4, K9J 2X5, K9J 7K6, K9J 7L4, K9J 8M6",
                "countryCode": "CA",
                "country": "Canada",
                "countryCodeISO3": "CAN",
                "freeformAddress": "Charlotte Street, Peterborough ON K9H, K9J",
                "localName": "Peterborough"
              },
              "position": {
                "lat": 44.30306,
                "lon": -78.32773
              },
              "viewport": {
                "topLeftPoint": {
                  "lat": 44.31154,
                  "lon": -78.34073
                },
                "btmRightPoint": {
                  "lat": 44.29355,
                  "lon": -78.31559
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
        $this->assertEquals('api.tomtom.com', $request->getUri()->getHost());
        $this->assertEquals('/search/2/geocode/test.json', $request->getUri()->getPath());
        $this->assertEquals('countrySet=US%2CCA&typeahead=true', $request->getUri()->getQuery());
    }

    public function testReverse()
    {
        $reverseJson = '{
          "summary": {
            "queryTime": 11,
            "numResults": 1
          },
          "addresses": [
            {
              "address": {
                "buildingNumber": "1001",
                "streetNumber": "1001",
                "routeNumbers": [],
                "street": "42nd Street",
                "streetName": "42nd Street",
                "streetNameAndNumber": "1001 42nd Street",
                "countryCode": "US",
                "countrySubdivision": "CA",
                "countrySecondarySubdivision": "Alameda",
                "municipality": "Oakland",
                "postalCode": "94608",
                "municipalitySubdivision": "Longfellow",
                "country": "United States",
                "countryCodeISO3": "USA",
                "freeformAddress": "1001 42nd Street, Emeryville, CA 94608",
                "boundingBox": {
                  "northEast": "37.832881,-122.276230",
                  "southWest": "37.832777,-122.276928",
                  "entity": "position"
                },
                "extendedPostalCode": "94608-3620",
                "countrySubdivisionName": "California",
                "localName": "Emeryville"
              },
              "position": "37.832844,-122.276688"
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
        $this->assertEquals('api.tomtom.com', $request->getUri()->getHost());
        $this->assertEquals('/search/2/reverseGeocode/34.52,60.52.json', $request->getUri()->getPath());
        $this->assertEquals('', $request->getUri()->getQuery());
    }

    private function mockHttpClient(string $mockData, array &$historyContainer)
    {
        $history = Middleware::history($historyContainer);
        $mock = new MockHandler([
            new Response(200, [], $mockData),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        app()->bind(TomTomMapSearchClient::class, function ($app) use ($stack) {
            return new TomTomMapSearchClient(['handler' => $stack]);
        });
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getConcreteService()
    {
        app()->bind(MapSearchServiceInterface::class, TomTomMapSearchService::class);

        return app()->make(MapSearchServiceInterface::class);
    }
}
