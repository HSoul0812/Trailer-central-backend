<?php

declare(strict_types=1);

namespace Tests\Unit\App\Transformers\MapSearch;

use App\DTOs\MapSearch\HereResponse;
use App\Transformers\MapSearch\HereResponseTransformer;
use Tests\Common\UnitTestCase;

class HereMapSearchTransformerTest extends UnitTestCase
{
    public function transformDataProvider(): array
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
        $expectedGeocodeResponse = '[{
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
              }
        }]';
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
        $expectedAutocompleteResponse = '[{
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
            "position": null
        }]';

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
        $expectedReverseResponse = '[{
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
            }
        }]';

        return [
            [$geocodeJson, $expectedGeocodeResponse],
            [$autocompleteJson, $expectedAutocompleteResponse],
            [$reverseJson, $expectedReverseResponse],
        ];
    }

    /**
     * @dataProvider transformDataProvider
     */
    public function testTransformer($json, $expectedResponse)
    {
        $jsonObject = HereResponse::fromData(json_decode($json, true));
        $transformer = new HereResponseTransformer();
        $response = $transformer->transform($jsonObject);
        $this->assertJsonStringEqualsJsonString(json_encode($response), $expectedResponse);
    }
}
