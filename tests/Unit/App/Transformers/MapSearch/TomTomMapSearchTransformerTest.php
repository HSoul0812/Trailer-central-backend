<?php

declare(strict_types=1);

namespace Tests\Unit\App\Transformers\MapSearch;

use App\DTOs\MapSearch\TomTomGeocodeResponse;
use App\DTOs\MapSearch\TomTomReverseGeocodeResponse;
use App\Transformers\MapSearch\TomTomGeocodeResponseTransformer;
use App\Transformers\MapSearch\TomTomReverseGeocodeResponseTransformer;
use Tests\Common\UnitTestCase;

class TomTomMapSearchTransformerTest extends UnitTestCase
{
    public function transformGeocodeDataProvider(): array
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
        $expectedAutocompleteResponse = '[
            {
                "address":{
                    "label":"Charlotte Street, Peterborough ON K9H, K9J",
                    "countryCode":"CA",
                    "countryName":"Canada",
                    "stateCode":"ON",
                    "state":"Ontario",
                    "county":null,
                    "city":"Peterborough",
                    "district":null,
                    "street":"Charlotte Street",
                    "postalCode":"K9H, K9J"
                },
                "position":{
                    "lat":44.30306,
                    "lng":-78.32773
                }
            }
        ]';

        return [
            [$autocompleteJson, $expectedAutocompleteResponse],
        ];
    }

    public function transformReverseGeocodeDataProvider(): array
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
        $expectedReverseResponse = '[
            {
                "address":{
                    "label":"1001 42nd Street, Emeryville, CA 94608",
                    "countryCode":"US",
                    "countryName":"United States",
                    "stateCode":"CA",
                    "state":"California",
                    "county":"Alameda",
                    "city":"Oakland",
                    "district":"Longfellow",
                    "street":"42nd Street",
                    "postalCode":"94608"
                },
                "position":"37.832844,-122.276688"
            }
        ]';

        return [
            [$reverseJson, $expectedReverseResponse],
        ];
    }

    /**
     * @dataProvider transformGeocodeDataProvider
     */
    public function testGeocodeTransformer($json, $expectedResponse)
    {
        $jsonObject = TomTomGeocodeResponse::fromData(json_decode($json, true));
        $transformer = new TomTomGeocodeResponseTransformer();
        $response = $transformer->transform($jsonObject);
        $this->assertJsonStringEqualsJsonString(json_encode($response), $expectedResponse);
    }

    /**
     * @dataProvider transformReverseGeocodeDataProvider
     */
    public function testReverseGeocodeTransformer($json, $expectedResponse)
    {
        $jsonObject = TomTomReverseGeocodeResponse::fromData(json_decode($json, true));
        $transformer = new TomTomReverseGeocodeResponseTransformer();
        $response = $transformer->transform($jsonObject);
        $this->assertJsonStringEqualsJsonString(json_encode($response), $expectedResponse);
    }
}
