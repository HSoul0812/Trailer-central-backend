<?php

namespace Tests\Unit\App\Services\Inventory;

use App\Models\Geolocation\Geolocation;
use App\Repositories\Geolocation\GeolocationRepositoryInterface;
use App\Services\Inventory\InventoryService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Common\TestCase;

class InventoryServiceTest extends TestCase
{
    private InventoryService $service;
    private Client $httpClient;
    private MockObject $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->getConcreteService();
    }

    public function testListWithNoLocation() {
        $this->repository->expects($this->once())
            ->method('get')
            ->will($this->throwException(new ModelNotFoundException()));

        $response = $this->service->list([
            'location_type' => 'region',
            'location_region' => 'CA',
            'location_city' => '123'
        ]);
        $this->assertEquals(154, $response->inventories->total());
        $this->assertEquals(1, $response->inventories->count());
        $this->assertEquals('1000022126', $response->inventories->items()[0]->id);
    }

    public function testListWithValidLocation() {
        $geolocation = new Geolocation();
        $geolocation->latitude = 30.00;
        $geolocation->longitude = -30.00;

        $this->repository->expects($this->once())
            ->method('get')
            ->with(['city' => '123', 'state' => 'CA', 'country' => 'USA'])
            ->will($this->returnValue($geolocation));

        $response = $this->service->list([
            'location_type' => 'region',
            'location_region' => 'CA',
            'location_city' => '123'
        ]);
        $this->assertEquals(154, $response->inventories->total());
        $this->assertEquals(1, $response->inventories->count());
        $this->assertEquals('1000022126', $response->inventories->items()[0]->id);
    }

    private function getConcreteService(): InventoryService
    {
        $this->httpClient = $this->mockHttpClient();
        $this->repository = $this->mockRepository();
        return new InventoryService($this->httpClient, $this->repository);
    }

    private function mockRepository(): MockObject {
        return $this->createMock(GeolocationRepositoryInterface::class);
    }

    private function mockHttpClient(): Client {
        $mockData = '{
          "took": 8,
          "timed_out": false,
          "_shards": {
            "total": 5,
            "successful": 5,
            "skipped": 0,
            "failed": 0
          },
          "hits": {
            "total": 154,
            "max_score": 25.873837,
            "hits": [
              {
                "_index": "inventory20220106x04",
                "_type": "inventory",
                "_id": "1000022126",
                "_score": 25.873837,
                "_source": {
                  "id": "1000022126",
                  "isActive": true,
                  "dealerId": "1001",
                  "dealerLocationId": "9437",
                  "createdAt": "2021-09-16 19:39:37",
                  "updatedAt": "2021-09-17 02:17:28",
                  "updatedAtUser": "2021-09-16 22:17:23",
                  "isSpecial": false,
                  "isFeatured": false,
                  "isArchived": false,
                  "stock": "30688",
                  "title": "22 Grey Wolf 21GP Other",
                  "year": "22",
                  "manufacturer": "Grey Wolf",
                  "model": "21GP",
                  "description": "",
                  "status": "3",
                  "category": "other",
                  "useWebsitePrice": false,
                  "condition": "used",
                  "length": "0.00",
                  "width": "0.00",
                  "height": "0.00",
                  "showOnKsl": false,
                  "showOnRacingjunk": false,
                  "showOnWebsite": true,
                  "dealer.name": "Marcel Test Dealership",
                  "dealer.email": "ben@plze.net",
                  "location.name": "Trailer World Alabama Ozark - Main Office",
                  "location.email": "chris@trailerworldalabama.com",
                  "location.contact": "David",
                  "location.website": "www.trailerworldalabama.com",
                  "location.phone": "3344450650",
                  "location.address": "1936 County Rd 11",
                  "location.city": "OZARK",
                  "location.region": "AL",
                  "location.postalCode": "36360",
                  "location.country": "US",
                  "widthInches": "0.00",
                  "heightInches": "0.00",
                  "lengthInches": "0.00",
                  "widthDisplayMode": "inches",
                  "heightDisplayMode": "inches",
                  "lengthDisplayMode": "inches",
                  "location.geo": {
                    "lat": "31.430000000000",
                    "lon": "-85.640000000000"
                  },
                  "keywords": [],
                  "availability": "on_order",
                  "availabilityLabel": "On Order",
                  "typeLabel": "Trailer",
                  "categoryLabel": "Other Trailer",
                  "basicPrice": null,
                  "originalWebsitePrice": null,
                  "websitePrice": null,
                  "existingPrice": null,
                  "numAxles": null,
                  "frameMaterial": null,
                  "pullType": null,
                  "numStalls": null,
                  "loadType": null,
                  "roofType": null,
                  "noseType": null,
                  "color": null,
                  "numSleeps": null,
                  "numAc": null,
                  "fuelType": null,
                  "isRental": false,
                  "numSlideouts": null,
                  "numBatteries": null,
                  "horsepower": null,
                  "numPassengers": null,
                  "conversion": null,
                  "cabType": null,
                  "engineSize": null,
                  "transmission": null,
                  "driveTrail": null,
                  "floorplan": null,
                  "propulsion": null,
                  "featureList": [],
                  "image": "",
                  "images": [
                    ""
                  ],
                  "imagesSecondary": [
                    ""
                  ]
                }
              }
            ]
          },
          "aggregations": {
            "filter_aggs": {
              "doc_count": 154,
              "pull_type": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": []
              },
              "color": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": []
              },
              "slideouts": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": []
              },
              "configuration": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": []
              },
              "year": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": [
                  {
                    "key": 22,
                    "doc_count": 151
                  },
                  {
                    "key": 2020,
                    "doc_count": 2
                  },
                  {
                    "key": 2021,
                    "doc_count": 1
                  }
                ]
              },
              "length": {
                "count": 154,
                "min": 0.0,
                "max": 0.0,
                "avg": 0.0,
                "sum": 0.0
              },
              "height_inches": {
                "count": 154,
                "min": 0.0,
                "max": 0.0,
                "avg": 0.0,
                "sum": 0.0
              },
              "axles": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": []
              },
              "manufacturer": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 25,
                "buckets": [
                  {
                    "key": "grey wolf",
                    "doc_count": 65
                  },
                  {
                    "key": "other",
                    "doc_count": 17
                  },
                  {
                    "key": "reflection",
                    "doc_count": 10
                  },
                  {
                    "key": "skyline",
                    "doc_count": 8
                  },
                  {
                    "key": "magnum",
                    "doc_count": 6
                  },
                  {
                    "key": "minnie winnie",
                    "doc_count": 5
                  },
                  {
                    "key": "montana",
                    "doc_count": 5
                  },
                  {
                    "key": "travalong",
                    "doc_count": 5
                  },
                  {
                    "key": "cherokee",
                    "doc_count": 4
                  },
                  {
                    "key": "solesbee",
                    "doc_count": 4
                  }
                ]
              },
              "condition": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": [
                  {
                    "key": "used",
                    "doc_count": 154
                  }
                ]
              },
              "length_inches": {
                "count": 154,
                "min": 0.0,
                "max": 0.0,
                "avg": 0.0,
                "sum": 0.0
              },
              "width_inches": {
                "count": 154,
                "min": 0.0,
                "max": 0.0,
                "avg": 0.0,
                "sum": 0.0
              },
              "price": {
                "count": 3,
                "min": 120.0,
                "max": 3000.0,
                "avg": 1706.6666666666667,
                "sum": 5120.0
              },
              "dealer_location_id": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": [
                  {
                    "key": 9437,
                    "doc_count": 150
                  },
                  {
                    "key": 11998,
                    "doc_count": 3
                  },
                  {
                    "key": 12084,
                    "doc_count": 1
                  }
                ]
              },
              "width": {
                "count": 154,
                "min": 0.0,
                "max": 0.0,
                "avg": 0.0,
                "sum": 0.0
              },
              "construction": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": [
                  {
                    "key": "composite",
                    "doc_count": 1
                  }
                ]
              },
              "category": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": [
                  {
                    "key": "other",
                    "doc_count": 154
                  }
                ]
              },
              "stalls": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": []
              },
              "height": {
                "count": 154,
                "min": 0.0,
                "max": 0.0,
                "avg": 0.0,
                "sum": 0.0
              }
            },
            "pull_type": {
              "doc_count_error_upper_bound": 0,
              "sum_other_doc_count": 0,
              "buckets": []
            },
            "color": {
              "doc_count_error_upper_bound": 0,
              "sum_other_doc_count": 0,
              "buckets": []
            },
            "slideouts": {
              "doc_count_error_upper_bound": 0,
              "sum_other_doc_count": 0,
              "buckets": []
            },
            "configuration": {
              "doc_count_error_upper_bound": 0,
              "sum_other_doc_count": 0,
              "buckets": []
            },
            "year": {
              "doc_count_error_upper_bound": 0,
              "sum_other_doc_count": 0,
              "buckets": [
                {
                  "key": 22,
                  "doc_count": 151
                },
                {
                  "key": 2020,
                  "doc_count": 2
                },
                {
                  "key": 2021,
                  "doc_count": 1
                }
              ]
            },
            "length": {
              "count": 154,
              "min": 0.0,
              "max": 0.0,
              "avg": 0.0,
              "sum": 0.0
            },
            "height_inches": {
              "count": 154,
              "min": 0.0,
              "max": 0.0,
              "avg": 0.0,
              "sum": 0.0
            },
            "axles": {
              "doc_count_error_upper_bound": 0,
              "sum_other_doc_count": 0,
              "buckets": []
            },
            "manufacturer": {
              "doc_count_error_upper_bound": 0,
              "sum_other_doc_count": 25,
              "buckets": [
                {
                  "key": "grey wolf",
                  "doc_count": 65
                },
                {
                  "key": "other",
                  "doc_count": 17
                },
                {
                  "key": "reflection",
                  "doc_count": 10
                },
                {
                  "key": "skyline",
                  "doc_count": 8
                },
                {
                  "key": "magnum",
                  "doc_count": 6
                },
                {
                  "key": "minnie winnie",
                  "doc_count": 5
                },
                {
                  "key": "montana",
                  "doc_count": 5
                },
                {
                  "key": "travalong",
                  "doc_count": 5
                },
                {
                  "key": "cherokee",
                  "doc_count": 4
                },
                {
                  "key": "solesbee",
                  "doc_count": 4
                }
              ]
            }
          }
        }';

        $mock = new MockHandler([
            new Response(200, [], $mockData),
        ]);

        $stack = HandlerStack::create($mock);

        return new Client(['handler' => $stack]);
    }
}
