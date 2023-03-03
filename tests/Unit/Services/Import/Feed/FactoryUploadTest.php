<?php

namespace Tests\Unit\Services\Import\Feed;

use Mockery;
use Tests\TestCase;
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
    public function testCanAddInventory($code, $key, $type, $data) {
        $apiUpload = $this->getEloquentMock(FeedApiUpload::class);

        $this->uploadRepository
            ->shouldReceive('createOrUpdate')
            ->with([
                'key' => $key,
                'code' => $code,
                'type' => $type,
                'data' => $data,
            ])
            ->once()
            ->andReturn($apiUpload);

        $upload = app(FactoryUpload::class);
        $upload->run(json_decode($data, true));
    }

    public function validDataProvider(): array
    {
        return [
            'Norstar' => [
                '{"code":"norstar","transactions":[{"action":"addInventory","parameters":{"stock":"DTB8314072ES2R50S62BLK","vin":"50HDB1422P1087058","model":"Dump","year":"2022","manufacturer":"NORSTAR","brand":"IRON BULL","condition":"new","msrp":"14467.15","price":"9910","category":"Dump","dealer_id":null,"dealer_name":"271 TRAILERS","dealer_email":null,"dealer_location_street":"2601 N Main St","dealer_location_city":"Paris","dealer_location_state":"TX","dealer_location_zip":"75460-9354","dealer_location_phone":"90378312","color_interior":"Black","color_exterior":"Black","attributes_length":"14","attributes_width":"83","attributes_gvwr":"14999","attributes_axle_capacity":"7000","attributes_axle_count":"2","attributes_hitch_type":"Bumper","ship_date":"2\\\/22\\\/2023","description":"DTB 83\" x 14\', 2-7K Axles","comments":"ES2 - 2 - 7,000 Lb Axles Straight (2 Elec. Brakes )\n000 - 6\" I-Beam Tongue\n000 - 6\" I-Beam Frame\nS62 - 48\" 10 ga. Dump Sides\n000 - Side Step Plate\n000 - ST235\\\/80 R16 LRE 10 Ply.\n000 - Bumper Pull Adj 14k Coupler 2 5\\\/16\n000 - Diamond Plate Fenders (Weld On)\n000 - Jack Spring Loaded Drop Leg 1-10K\n000 - Full Size Front Toolbox w\\\/Pump\n000 - Scissor Hoist TH-516\nR50 - Slide-IN Ramps 16\" x 80\"\n000 - Tarp System (Front Mount)\n000 - Lighting LED (OVAL 6\")\n000 - Standard Wiring Harness\n000 - D-Rings 3\" x 5\\\/8\" Weld On (4ech) Std.\n000 - Spare Tire Mt.\nC00 - Black\nBacked by IRONCLAD warranty \u2013 3 yr structural, 2 yr component, 2 yr free roadside assistance","photos":"https:\\/\\/statics.mynorstar.com\\/Quotes\\/Cap_7_cznrc{uzitymj{cu.png","source":"NORSTAR"}}]}'
            ],
            'Lt' => [
                '{"code":"lt","key":"4ZEDK2021N1276855","type":"inventory","data":{"code":"lt","transactions":[{"vin_no":"4ZEDK2021N1276855","descrip2":"102\\" x 20\' Deck Over Pintle Hook","model_yr":"2022","arcusto_id":"25581","arcusto_company":"JacksSon\'s Trailers, Inc.","ship_date":"2022-12-02","data1":"6\\" Channel Frame\\\\r2 - 7,000 Lb Dexter Spring Axles (2 Elec FSA Brakes)\\\\rST235\\\\\\/80 R16 LRE 10 Ply. (BLACK WHEELS)\\rCoupler 2-5\\/16\\" Adjustable (6 HOLE)\\rTreated Wood Floor\\rREAR Slide-IN Ramps 8\' x 16\\"\\r16\\" Cross-Members\\rJack Spring Loaded Drop Leg 1-10K\\rLights LED (w\\/Cold Weather Harness)\\r4 - D-Rings 3\\" Weld On\\rSpare Tire Mount\\rBlack (w\\\/Primer)\\rRoad Service Program 903-783-3933 for Info.\\r\\r","series":"DK14"}]}}'
            ],
            'Lgs' => [
                '{"code":"lt","key":"5JW7A1924P7081411","type":"inventory","data":{"0":"code\" => \"lt","transactions":[{"dateentered":"2022-07-12T00:00:00-04:00","vin":"5JW7A1924P7081411","cost":"5663.0000","model":"CASQA7.0X19TE2FF","dealerid":"216","dealer":"EDS Auto Inc","color":"030 WH\\\/C\\\/030 WH","reardoor":"RD","company":"Cargo Express","interiorheight":"78","frontnose":"W","modelname":"AX SNOW\\\/ATV ","trailertype":"SNOW"}]}}'
            ]
        ];
    }

    public function invalidDataProvider(): array
    {
        return [
            'Norstar without data' => [
                '{"code":"norstar","transactions":[]}'
            ],
            'Request without code' => [
                '{"key":"5JW7A1924P7081411","type":"inventory","data":{"0":"code\" => \"lt","transactions":[{"dateentered":"2022-07-12T00:00:00-04:00","vin":"5JW7A1924P7081411","cost":"5663.0000","model":"CASQA7.0X19TE2FF","dealerid":"216","dealer":"EDS Auto Inc","color":"030 WH\\\/C\\\/030 WH","reardoor":"RD","company":"Cargo Express","interiorheight":"78","frontnose":"W","modelname":"AX SNOW\\\/ATV ","trailertype":"SNOW"}]}}'
            ],
            'Empty Request' => [
                //
            ]
        ];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
