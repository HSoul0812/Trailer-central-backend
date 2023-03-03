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
     * @dataProvider validDataProvider
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
     * @dataProvider invalidDataProvider
     *
     * @throws PropertyDoesNotExists
     */
    public function testCantAddInventory($data) {
        $this->expectExceptionMessage('transactions invalid or not found in rawData');

        $upload = app(FactoryUpload::class);
        $upload->run(json_decode($data, true));
    }

    public function validDataProvider(): array
    {
        return [
            'Norstar' => [
                'code' => 'norstar',
                'type' => 'inventory',
                'vin' => '50HDB1422P1087058',
                'data' => '{
                    "code":"norstar",
                    "transactions": [
                        {
                            "action":"addInventory",
                            "parameters": {
                                "stock":"DTB8314072ES2R50S62BLK",
                                "vin":"50HDB1422P1087058",
                                "model":"Dump",
                                "year":"2022",
                                "manufacturer":"NORSTAR",
                                "brand":"IRON BULL",
                                "condition":"new",
                                "msrp":"14467.15",
                                "price":"9910",
                                "category":"Dump",
                                "dealer_id":null,
                                "dealer_name":"271 TRAILERS",
                                "dealer_email":null,
                                "dealer_location_street":"2601 N Main St",
                                "dealer_location_city":"Paris",
                                "dealer_location_state":"TX",
                                "dealer_location_zip":"75460-9354",
                                "dealer_location_phone":"90378312",
                                "color_interior":"Black",
                                "color_exterior":"Black",
                                "attributes_length":"14",
                                "attributes_width":"83",
                                "attributes_gvwr":"14999",
                                "attributes_axle_capacity":"7000",
                                "attributes_axle_count":"2",
                                "attributes_hitch_type":"Bumper",
                                "ship_date":"2/22/2023",
                                "photos":"https://statics.mynorstar.com/Quotes/Cap_7_cznrc{uzitymj{cu.png",
                                "source":"NORSTAR"
                            }
                        }
                    ]
                }'
            ],
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

    public function tearDown(): void
    {
        Mockery::close();
    }
}
