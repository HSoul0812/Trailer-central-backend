<?php

namespace Tests\Unit\Services\Import\Feed\Type;

use App\Repositories\Feed\FeedApiUploadsRepository;
use App\Services\Import\Feed\Type\Norstar;
use Tests\TestCase;

class NorstarTest extends TestCase
{
    function testCanAddInventory()
    {
        $this->mock(FeedApiUploadsRepository::class, function($mock) {
            $json = '{"locationId":41173,"vin":"50HTL2632K1028623","condition":"new","status":"available","year":2019,"manufacturer":"Norstar","model":"TLB0226073_25484_1","category":"TILT","price":"8588","msrp":"8588","length":"26","width":"02","height":"N\/A","weight":"4600","gvwr":"21000","axleCapacity":0,"payloadCapacity":16400,"costOfUnit":"8588","costOfShipping":"0","costOfPrep":"N\/A","numAc":"N\/A","numAxles":"3","numBatteries":"N\/A","numPassengers":"N\/A","numSleeps":"N\/A","numSlideouts":"N\/A","numStalls":"N\/A","color":"black","pullType":"B","noseType":"N\/A","roofType":"N\/A","loadType":"N\/A","fuelType":"N\/A","frameMaterial":"60","horsepower":"N\/A","hasLq":false,"hasManger":false,"hasMidtack":false,"hasRamps":false,"mileage":"N\/A","isRental":false}';
            $mock->shouldReceive('createOrUpdate')->with([
                'code' => 'norstar',
                'key' => '50HTL2632K1028623',
                'type' => 'inventory',
                'data' => $json,
            ], 'norstar', '50HTL2632K1028623');

            // for addDealer
            $mock->shouldReceive('createOrUpdate')->once();
        });

        /** @var Norstar $norstar */
        $norstar = app(Norstar::class);
        $norstar->run(json_decode($this->data, true));
    }

    function testCanAddDealer()
    {
        $this->mock(FeedApiUploadsRepository::class, function($mock) {
            // ignore addInventory
            $mock->shouldReceive('createOrUpdate')->once();

            // for addDealer
            $mock->shouldReceive('createOrUpdate')->with([
                'code' => 'norstar',
                'key' => 12312,
                'type' => 'dealer',
                'data' => '{"locationId":41173,"dealerId":12312,"address":"100 Main Street","city":"Dodge","state":"Nevada","zip":"54321","name":"ACME corp","locationName":"Some Location"}'
            ], 'norstar', 12312);
        });

        /** @var Norstar $norstar */
        $norstar = app(Norstar::class);
        $norstar->run(json_decode($this->data, true));
    }

    private $data = '{
    "transactions": [
        {
            "action": "addInventory",
            "parameters": {
                "locationId": 41173,
                "vin": "50HTL2632K1028623",
                "condition": "new",
                "status": "available",
                "year": 2019,
                "manufacturer": "Norstar",
                "model": "TLB0226073_25484_1",
                "category": "TILT",
                "price": "8588",
                "msrp": "8588",
                "length": "26",
                "width": "02",
                "height": "N/A",
                "weight": "4600",
                "gvwr": "21000",
                "axleCapacity": 0,
                "payloadCapacity": 16400,
                "costOfUnit": "8588",
                "costOfShipping": "0",
                "costOfPrep": "N/A",
                "numAc": "N/A",
                "numAxles": "3",
                "numBatteries": "N/A",
                "numPassengers": "N/A",
                "numSleeps": "N/A",
                "numSlideouts": "N/A",
                "numStalls": "N/A",
                "color": "black",
                "pullType": "B",
                "noseType": "N/A",
                "roofType": "N/A",
                "loadType": "N/A",
                "fuelType": "N/A",
                "frameMaterial": "60",
                "horsepower": "N/A",
                "hasLq": false,
                "hasManger": false,
                "hasMidtack": false,
                "hasRamps": false,
                "mileage": "N/A",
                "isRental": false
            }
        },
        {
            "action": "addDealer",
            "parameters": {
                "locationId": 41173,
                "dealerId": 12312,
                "address": "100 Main Street",
                "city": "Dodge",
                "state": "Nevada",
                "zip": "54321",
                "name": "ACME corp",
                "locationName": "Some Location"
            }
        }
    ]
}';
}
