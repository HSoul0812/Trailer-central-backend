<?php

namespace Tests\Unit\Services\Import\Feed;

use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use App\Exceptions\PropertyDoesNotExists;
use App\Models\Feed\Uploads\FeedApiUpload;
use App\Services\Import\Feed\FactoryUpload;
use App\Repositories\Feed\FeedApiUploadsRepository;

/**
 * class App\Services\Import\Feed
 *
 * @coversDefaultClass \App\Services\Import\Feed\FactoryUpload
 *
 * @group Integrations
 * @group FactoryFeeds
 *
 * @package Tests\Unit\Services\Import\Feed
 */
class FactoryUploadTest extends TestCase
{

    /**
     * @var FeedApiUploadsRepository
     */
    private $uploadRepository;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->uploadRepository = Mockery::mock(FeedApiUploadsRepository::class);
        $this->app->instance(FeedApiUploadsRepository::class, $this->uploadRepository);
    }

    /**
     *
     * @dataProvider validInventoryDataProvider
     *
     * @throws PropertyDoesNotExists
     */
    public function testCanAddInventory($code, $type, $vin, $data) {
        $decode = json_decode($data, true);
        $inventory = json_encode($decode['transactions'][0]['parameters']);

        $this->uploadRepository
            ->shouldReceive('createOrUpdate')
            ->with([
                'code' => $code,
                'key' => $vin,
                'type' => $type,
                'data' => $inventory,
            ], $code, $vin)
            ->once();

        Log::shouldReceive('info')->with("{$code} Import: adding inventory with VIN: " . $vin);

        $upload = app(FactoryUpload::class);
        $result = $upload->run(json_decode($data, true));

        $this->assertNull($result);
    }

    /**
     *
     * @dataProvider validDealerDataProvider
     *
     * @throws PropertyDoesNotExists
     */
    public function testCanAddDealer($code, $type, $dealerId, $data) {
        $decode = json_decode($data, true);
        $transaction = $decode['transactions'][0];

        $this->uploadRepository
            ->shouldReceive('createOrUpdate')
            ->with([
                'code' => $code,
                'key' => $dealerId,
                'type' => $type,
                'data' => json_encode($transaction['parameters']),
            ], $code, $dealerId)
            ->once();

        Log::shouldReceive('info')->with("{$code} Import: adding dealer", [
            'dealer' => $transaction['parameters']
        ]);

        $upload = app(FactoryUpload::class);
        $result = $upload->run(json_decode($data, true));

        $this->assertNull($result);
    }

    /**
     *
     * @dataProvider invalidDataProvider
     *
     * @throws PropertyDoesNotExists
     */
    public function testCantAddInventory($code, $type, $vin, $data) {
        $decode = json_decode($data, true);

        if (empty($decode['transactions'])) {
            $this->expectExceptionMessage('transactions invalid or not found in rawData');
        }

        $upload = app(FactoryUpload::class);
        $upload->run($decode);
    }

    /**
     *
     * @dataProvider invalidEmptyVinOrVinNoDataProvider
     *
     * @throws PropertyDoesNotExists
     */
    public function testInventoryWithoutVinOrVinNo($code, $type, $vin, $data) {
        $decode = json_decode($data, true);
        $inventory = json_encode($decode['transactions'][0]['parameters']);

        $this->assertTrue(empty($inventory['vin']) && empty($inventory['vin_no']));

        $this->uploadRepository
            ->shouldNotReceive('createOrUpdate');

        $upload = app(FactoryUpload::class);
        $upload->run($decode);
    }

    /**
     *
     * @dataProvider invalidActionDataProvider
     *
     * @throws PropertyDoesNotExists
     */
    public function testTransactionWithInvalidAction($code, $type, $vin, $data) {
        $decode = json_decode($data, true);
        $transaction = $decode['transactions'][0];

        $this->assertTrue($transaction['action'] !== 'addInventory' && $transaction['action'] !== 'addDealer');

        Log::shouldReceive('warning')->with("{$code} Import: invalid action {$transaction['action']}");

        $this->uploadRepository
            ->shouldNotReceive('createOrUpdate');

        $upload = app(FactoryUpload::class);
        $upload->run($decode);
    }

    /**
     *
     * @dataProvider invalidEmptyActionDataProvider
     *
     * @throws PropertyDoesNotExists
     */
    public function testInventoryWithoutTransactionAction($code, $type, $vin, $data) {
        $decode = json_decode($data, true);
        $inventory = json_encode($decode['transactions'][0]['parameters']);

        $this->assertTrue(empty($inventory['action']));

        $this->uploadRepository
            ->shouldNotReceive('createOrUpdate');

        $upload = app(FactoryUpload::class);
        $upload->run($decode);
    }

    public function validInventoryDataProvider(): array
    {
        return [
            'Lt' => [
                'code' => 'lt',
                'type' => 'inventory',
                'vin' => '4ZEDK2021N1276855',
                'data' => '{
                    "code": "lt",
                    "transactions": [
                        {
                            "action":"addInventory",
                            "parameters": {
                                "vin_no": "4ZEDK2021N1276855",
                                "descrip2": "102\" x 20\' Deck Over Pintle Hook",
                                "model_yr": "2022",
                                "arcusto_id": "25581",
                                "arcusto_company": "JacksSon\'s Trailers, Inc.",
                                "ship_date": "2022-12-02",
                                "data1": "6\" Channel Frame\r2 - 7,000 Lb Dexter Spring Axles (2 Elec FSA Brakes)\rST235\/80 R16 LRE 10 Ply. (BLACK WHEELS)\rCoupler 2-5\/16\" Adjustable (6 HOLE)\rTreated Wood Floor\rREAR Slide-IN Ramps 8\' x 16\"\r16\" Cross-Members\rJack Spring Loaded Drop Leg 1-10K\rLights LED (w\/Cold Weather Harness)\r4 - D-Rings 3\" Weld On\rSpare Tire Mount\rBlack (w\/Primer)\rRoad Service Program 903-783-3933 for Info.\r\r",
                                "series": "DK14"
                            }
                        }
                    ]
                }'
            ],
            'Lgs' => [
                'code' => 'lgs',
                'type' => 'inventory',
                'vin' => '5JW7A1924P7081411',
                'data' => '{
                    "code": "lgs",
                    "transactions": [
                        {
                            "action":"addInventory",
                            "parameters": {
                                "dateentered": "2022-07-12T00:00:00-04:00",
                                "vin": "5JW7A1924P7081411",
                                "cost": "5663.0000",
                                "model": "CASQA7.0X19TE2FF",
                                "dealerid": "216",
                                "dealer": "EDS Auto Inc",
                                "color": "030 WH\/C\/030 WH",
                                "reardoor": "RD",
                                "company": "Cargo Express",
                                "interiorheight": "78",
                                "frontnose": "W",
                                "modelname": "AX SNOW\/ATV ",
                                "trailertype": "SNOW"
                            }
                        }
                    ]
                }'
            ]
        ];
    }

    public function validDealerDataProvider(): array
    {
        return [
            'Norstar' => [
                'code' => 'norstar',
                'type' => 'dealer',
                'dealerId' => '12312',
                'data' => '{
                    "code":"norstar",
                    "transactions": [
                        {
                            "action":"addDealer",
                            "parameters": {
                                "locationId":41173,
                                "dealerId":12312,
                                "address":"100 Main Street",
                                "city":"Dodge",
                                "state":"Nevada",
                                "zip":"54321",
                                "name":"ACME corp",
                                "locationName":"Some Location"
                            }
                        }
                    ]
                }'
            ]
        ];
    }

    public function invalidDataProvider(): array
    {
        return [
            'Norstar without data' => [
                'code' => 'norstar',
                'type' => 'inventory',
                'vin' => '50HDB1422P1087058',
                'data' => '{
                    "code":"norstar",
                    "transactions": []
                }'
            ]
        ];
    }

    public function invalidEmptyVinOrVinNoDataProvider(): array
    {
        return [
            'Lt without vin or vin_no' => [
                'code' => 'lt',
                'type' => 'inventory',
                'vin' => '',
                'data' => '{
                    "code":"lt",
                    "transactions": [
                        {
                            "action":"addInventory",
                            "parameters": {
                                "descrip2": "102\" x 20\' Deck Over Pintle Hook",
                                "model_yr": "2022",
                                "arcusto_id": "25581",
                                "arcusto_company": "JacksSon\'s Trailers, Inc.",
                                "ship_date": "2022-12-02",
                                "data1": "6\" Channel Frame\r2 - 7,000 Lb Dexter Spring Axles (2 Elec FSA Brakes)\rST235\/80 R16 LRE 10 Ply. (BLACK WHEELS)\rCoupler 2-5\/16\" Adjustable (6 HOLE)\rTreated Wood Floor\rREAR Slide-IN Ramps 8\' x 16\"\r16\" Cross-Members\rJack Spring Loaded Drop Leg 1-10K\rLights LED (w\/Cold Weather Harness)\r4 - D-Rings 3\" Weld On\rSpare Tire Mount\rBlack (w\/Primer)\rRoad Service Program 903-783-3933 for Info.\r\r",
                                "series": "DK14"
                            }
                        }
                    ]
                }'
            ]
        ];
    }

    public function invalidEmptyActionDataProvider(): array
    {
        return [
            'Lt with empty action' => [
                'code' => 'lt',
                'type' => 'inventory',
                'vin' => '4ZEDK2021N1276855',
                'data' => '{
                    "code":"lt",
                    "transactions": [
                        {
                            "action":"",
                            "parameters": {
                                "vin_no": "4ZEDK2021N1276855",
                                "descrip2": "102\" x 20\' Deck Over Pintle Hook",
                                "model_yr": "2022",
                                "arcusto_id": "25581",
                                "arcusto_company": "JacksSon\'s Trailers, Inc.",
                                "ship_date": "2022-12-02",
                                "data1": "6\" Channel Frame\r2 - 7,000 Lb Dexter Spring Axles (2 Elec FSA Brakes)\rST235\/80 R16 LRE 10 Ply. (BLACK WHEELS)\rCoupler 2-5\/16\" Adjustable (6 HOLE)\rTreated Wood Floor\rREAR Slide-IN Ramps 8\' x 16\"\r16\" Cross-Members\rJack Spring Loaded Drop Leg 1-10K\rLights LED (w\/Cold Weather Harness)\r4 - D-Rings 3\" Weld On\rSpare Tire Mount\rBlack (w\/Primer)\rRoad Service Program 903-783-3933 for Info.\r\r",
                                "series": "DK14"
                            }
                        }
                    ]
                }'
            ]
        ];
    }

    public function invalidActionDataProvider(): array
    {
        return [
            'Lt with invalid action' => [
                'code' => 'lt',
                'type' => 'inventory',
                'vin' => '4ZEDK2021N1276855',
                'data' => '{
                    "code":"lt",
                    "transactions": [
                        {
                            "action":"addUnit",
                            "parameters": {
                                "vin_no": "4ZEDK2021N1276855",
                                "descrip2": "102\" x 20\' Deck Over Pintle Hook",
                                "model_yr": "2022",
                                "arcusto_id": "25581",
                                "arcusto_company": "JacksSon\'s Trailers, Inc.",
                                "ship_date": "2022-12-02",
                                "data1": "6\" Channel Frame\r2 - 7,000 Lb Dexter Spring Axles (2 Elec FSA Brakes)\rST235\/80 R16 LRE 10 Ply. (BLACK WHEELS)\rCoupler 2-5\/16\" Adjustable (6 HOLE)\rTreated Wood Floor\rREAR Slide-IN Ramps 8\' x 16\"\r16\" Cross-Members\rJack Spring Loaded Drop Leg 1-10K\rLights LED (w\/Cold Weather Harness)\r4 - D-Rings 3\" Weld On\rSpare Tire Mount\rBlack (w\/Primer)\rRoad Service Program 903-783-3933 for Info.\r\r",
                                "series": "DK14"
                            }
                        }
                    ]
                }'
            ]
        ];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
