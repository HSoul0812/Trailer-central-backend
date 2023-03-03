<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Feed\Factory\Import;

use Mockery;

use App\Repositories\Feed\FeedApiUploadsRepository;
use App\Services\Import\Feed\FactoryUpload;

use App\Exceptions\Common\BusyJobException;
use App\Http\Controllers\v1\Feed\UploadController;
use App\Http\Requests\Feed\Factory\UploadFactoryFeedUnitRequest;
use App\Jobs\Import\Feed\DealerFeedImporterJob;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Ramsey\Uuid\Uuid;
use Exception;
use Tests\Integration\IntegrationTestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Queue;

/**
 * class UploadControllerTest
 *
 * @covers \App\Http\Controllers\v1\Feed\UploadController
 *
 * @group Integrations
 * @group FactoryFeeds
 *
 * @package Tests\Integration\Http\Controllers\Feed\Factory\Import
 */
class UploadControllerTest extends IntegrationTestCase
{

    /**
     * @var FactoryUpload
     */
    private $factoryUpload;

    /**
     * @var FeedApiUploadsRepository
     */
    private $uploadRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->factoryUpload = Mockery::mock(FactoryUpload::class);
        $this->app->instance(FactoryUpload::class, $this->factoryUpload);

        $this->uploadRepository = Mockery::mock(FeedApiUploadsRepository::class);
        $this->app->instance(FeedApiUploadsRepository::class, $this->uploadRepository);
    }


    /**
     * @dataProvider validParametersForUploadProvider
     *
     * @covers ::upload
     *
     * @throws BusyJobException
     * @throws Exception
     */
    public function testUploadWithValidParameters($request): void
    {
        Queue::fake();

        // Using the controller "UploadController"
        $controller = app(UploadController::class);

        // And I have a well-formed "UploadFactoryFeedUnitRequest" request
        $request = new UploadFactoryFeedUnitRequest(
            json_decode($request, true)
        );

        // Assert the validation passes
        $this->assertTrue($request->validate());

        $code = $request->code;

        // And run the factory upload once
        $this->factoryUpload->shouldReceive('run')->once();

        // When I call the upload action using the well-formed request
        $response = $controller->upload($request, $code);

        // Then I should see that job wit a specific name was enqueued
        Queue::assertPushedOn('factory-feeds', DealerFeedImporterJob::class, function($job) use ($code) {
            $job->handle($this->factoryUpload);
            return true;
        });

        // And I should see that response status is 200*/
        self::assertEquals(JsonResponse::HTTP_OK, $response->status());
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
    */
    public function validParametersForUploadProvider(): array
    {
        return [
            'Norstar single transaction' => [
                '{
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
                                "ship_date":"2\\\/22\\\/2023",
                                "description":"DTB 83\" x 14\', 2-7K Axles",
                                "comments":"ES2 - 2 - 7,000 Lb Axles Straight (2 Elec. Brakes )\n000 - 6\" I-Beam Tongue\n000 - 6\" I-Beam Frame\nS62 - 48\" 10 ga. Dump Sides\n000 - Side Step Plate\n000 - ST235\\\/80 R16 LRE 10 Ply.\n000 - Bumper Pull Adj 14k Coupler 2 5\\\/16\n000 - Diamond Plate Fenders (Weld On)\n000 - Jack Spring Loaded Drop Leg 1-10K\n000 - Full Size Front Toolbox w\\\/Pump\n000 - Scissor Hoist TH-516\nR50 - Slide-IN Ramps 16\" x 80\"\n000 - Tarp System (Front Mount)\n000 - Lighting LED (OVAL 6\")\n000 - Standard Wiring Harness\n000 - D-Rings 3\" x 5\\\/8\" Weld On (4ech) Std.\n000 - Spare Tire Mt.\nC00 - Black\nBacked by IRONCLAD warranty \u2013 3 yr structural, 2 yr component, 2 yr free roadside assistance",
                                "photos":"https:\\/\\/statics.mynorstar.com\\/Quotes\\/Cap_7_cznrc{uzitymj{cu.png",
                                "source":"NORSTAR"
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
