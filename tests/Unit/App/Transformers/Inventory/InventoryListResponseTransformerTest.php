<?php

namespace Tests\Unit\App\Transformers\Inventory;

use App\DTOs\Inventory\TcEsInventory;
use App\DTOs\Inventory\TcEsResponseInventoryList;
use App\Transformers\Inventory\InventoryListResponseTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Common\UnitTestCase;

require 'app/Helpers/System/helper.php';
class InventoryListResponseTransformerTest extends UnitTestCase
{
    public function testTransform()
    {
        $inventoryJson = '{
          "id": "1000022125",
          "isActive": true,
          "dealerId": "1001",
          "dealerLocationId": "9437",
          "createdAt": "2021-09-16 19:39:37",
          "updatedAt": "2021-09-17 02:17:28",
          "updatedAtUser": "2021-09-16 22:17:23",
          "isSpecial": false,
          "isFeatured": false,
          "isArchived": false,
          "stock": "30687",
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
          "image": "/blog/blog.jpg",
          "images": [
            "/blog/blog.jpg"
          ],
          "imagesSecondary": [
            ""
          ]
        }';

        $json = json_decode($inventoryJson, true);
        $inventory = TcEsInventory::fromData($json);
        $paginator = new LengthAwarePaginator([$inventory], 1, 10, 1);
        $response = new TcEsResponseInventoryList();
        $response->inventories = $paginator;
        $response->aggregations = [];
        $response->limits = [];

        $transformer = new InventoryListResponseTransformer();
        $json = $transformer->transform($response);

        $this->assertEquals(1, count($json['inventories']));
        $this->assertEquals(1, $json['meta']['pagination']['total']);
        $this->assertEquals('1000022125', $json['inventories'][0]['id']);
        $this->assertEquals('https://dealer-cdn.com/blog/blog.jpg', $json['inventories'][0]['image']);
        $this->assertEquals('https://dealer-cdn.com/blog/blog.jpg', $json['inventories'][0]['images'][0]);
        $this->assertEquals('', $json['inventories'][0]['images_secondary'][0]);
    }
}
