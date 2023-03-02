<?php

namespace Tests\Integration\Http\Controllers\CRM\Leads;

use Tests\database\seeds\CRM\Leads\LeadTradeSeeder;
use Tests\Integration\IntegrationTestCase;
use Illuminate\Http\UploadedFile;
use Faker\Factory as Faker;
use Mockery;
use Mockery\LegacyMockInterface;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;
use App\Models\CRM\Leads\LeadTrade;
use App\Models\CRM\Leads\LeadTradeImage;

/**
 * Class LeadTradeControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Leads
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Leads\LeadTradeController
 */
class LeadTradeControllerTest extends IntegrationTestCase
{
    const TEST_VEHICLE_MAKE = 'Tesla';
    const TEST_VEHICLE_MODEL = 'Model S';
    const TEST_VEHICLE_YEAR = '2023';
    const TEST_NOTE = 'this is a note';

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker::create();
        
        $this->instanceMock('imageHelper', ImageHelper::class);
    }

    /**
     * @covers ::index
     * @group CRM
     */
    public function testIndex()
    {
        $leadTradeSeeder = new LeadTradeSeeder();
        $leadTradeSeeder->seed();

        $response = $this->json(
            'GET',
            '/api/leads/'. $leadTradeSeeder->lead->getKey() .'/trades?with=images',
            [],
            ['access-token' => $leadTradeSeeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'lead_id',
                        'type',
                        'make',
                        'model',
                        'year',
                        'price',
                        'length',
                        'width',
                        'notes',
                        'created_at',
                        'images' => [
                            '*' => [
                                'filename',
                                'path',
                            ]
                        ]
                    ]
                ]
            ]);

        $leadTrades = $leadTradeSeeder->leadTrades;

        $this->assertResponseDataEquals($response, $leadTrades, false);

        $leadTradeSeeder->cleanUp();
    }

    /**
     * @covers ::index
     * @group CRM
     */
    public function testIndexWithWrongAccessToken()
    {
        $leadTradeSeeder = new LeadTradeSeeder();
        $leadTradeSeeder->seed();

        $response = $this->json(
            'GET',
            '/api/leads/'. $leadTradeSeeder->lead->getKey() .'/trades?with=images',
            [],
            ['access-token' => 'wrong_access_token']
        );

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');

        $leadTradeSeeder->cleanUp();
    }

    /**
     * @covers ::create
     * @group CRM
     */
    public function testCreate()
    {
        $leadTradeSeeder = new LeadTradeSeeder();
        $leadTradeSeeder->seed();

        $randomStrings = [
            $this->faker->md5(),
            $this->faker->md5(),
            $this->faker->md5()
        ];

        $filenames = [
            'image1.jpg',
            'image2.png',
            'image3.bmp'
        ];

        // mock getRandomString()
        $this->imageHelper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRandomString')
            ->times(3)
            ->andReturn(
                $randomStrings[0],
                $randomStrings[1],
                $randomStrings[2]
            );

        $trade = $this->getEloquentMock(LeadTrade::class);
        $trade->shouldReceive('setRelation')->passthru();

        $response = $this->json(
            'POST',
            '/api/leads/'. $leadTradeSeeder->lead->getKey() .'/trades',
            [
                'make' => self::TEST_VEHICLE_MAKE,
                'model' => self::TEST_VEHICLE_MODEL,
                'year' => self::TEST_VEHICLE_YEAR,
                'notes' => self::TEST_NOTE,
                'images' => [
                    UploadedFile::fake()->create($filenames[0]),
                    UploadedFile::fake()->create($filenames[1]),
                    UploadedFile::fake()->create($filenames[2])
                ]
            ],
            ['access-token' => $leadTradeSeeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'lead_id',
                    'type',
                    'make',
                    'model',
                    'year',
                    'price',
                    'length',
                    'width',
                    'notes',
                    'created_at',
                    // 'images' => [
                    //     '*' => [
                    //         'filename',
                    //         'path',
                    //     ]
                    // ]
                ]
            ]);

        $response->assertStatus(200);

        $content = json_decode($response->getContent(), true);
        $tradeId = $content['data']['id'];

        $this->assertDatabaseHas('website_lead_trades', [
            'lead_id' => $leadTradeSeeder->lead->getKey(),
            'id' => $tradeId,
            'make' => self::TEST_VEHICLE_MAKE,
            'model' => self::TEST_VEHICLE_MODEL,
            'year' => self::TEST_VEHICLE_YEAR,
            'notes' => self::TEST_NOTE
        ]);

        $this->assertDatabaseHas('website_lead_trade_image', [
            'trade_id' => $tradeId,
            'filename' => $filenames[0],
            'path' => Storage::disk('s3')->url($randomStrings[0])
        ]);

        $this->assertDatabaseHas('website_lead_trade_image', [
            'trade_id' => $tradeId,
            'filename' => $filenames[1],
            'path' => Storage::disk('s3')->url($randomStrings[1])
        ]);

        $this->assertDatabaseHas('website_lead_trade_image', [
            'trade_id' => $tradeId,
            'filename' => $filenames[2],
            'path' => Storage::disk('s3')->url($randomStrings[2])
        ]);
        Storage::disk('s3')->assertExists($randomStrings);

        // cleanup
        $leadTradeSeeder->cleanUp();
        LeadTradeImage::where('trade_id', $tradeId)->delete();
        Storage::disk('s3')->delete($randomStrings);
    }

    /**
     * @covers ::update
     * @group CRM
     */
    public function testUpdate()
    {
        $leadTradeSeeder = new LeadTradeSeeder();
        $leadTradeSeeder->seed();

        $tradeId = $leadTradeSeeder->leadTrades[0]['id'];
        $leadTradeImageId1 = $leadTradeSeeder->leadTrades[0]['images'][0]['id'];

        // prepare test data
        // update image 1 with real data
        $randomString1 = $this->faker->md5();
        Storage::disk('s3')->put($randomString1, '');
        LeadTradeImage::find($leadTradeImageId1)->update([
            'filename' => 'existing_image1.png',
            'path' => Storage::disk('s3')->url($randomString1)
        ]);

        // add image 2
        $randomString2 = $this->faker->md5();
        Storage::disk('s3')->put($randomString2, '');
        $image2 = LeadTradeImage::create([
            'trade_id' => $tradeId,
            'filename' => 'existing_image2.png',
            'path' => Storage::disk('s3')->url($randomString2)
        ]);
        $leadTradeImageId2 = $image2->id;
        // end prepare test data

        // confirm test data
        $this->assertDatabaseHas('website_lead_trade_image', [
            'id' => $leadTradeImageId1,
            'trade_id' => $tradeId,
            'filename' => 'existing_image1.png',
            'path' => Storage::disk('s3')->url($randomString1)
        ]);

        $this->assertDatabaseHas('website_lead_trade_image', [
            'id' => $leadTradeImageId2,
            'trade_id' => $tradeId,
            'filename' => 'existing_image2.png',
            'path' => Storage::disk('s3')->url($randomString2)
        ]);

        Storage::disk('s3')->assertExists([
            $randomString1,
            $randomString2
        ]);
        //end confirm test data

        // mock getRandomString()
        $randomString3 = $this->faker->md5();
        $this->imageHelper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRandomString')
            ->once()
            ->andReturn($randomString3);

        $trade = $this->getEloquentMock(LeadTrade::class);
        $trade->shouldReceive('setRelation')->passthru();

        $response = $this->json(
            'POST',
            '/api/leads/'. $leadTradeSeeder->lead->getKey() .'/trades/' . $tradeId,
            [
                'make' => self::TEST_VEHICLE_MAKE,
                'model' => self::TEST_VEHICLE_MODEL,
                'year' => self::TEST_VEHICLE_YEAR,
                'notes' => self::TEST_NOTE,
                'new_images' => [
                    UploadedFile::fake()->create('new_image3.png')
                ],
                'existing_images' => [
                    [
                        'id' => $leadTradeImageId2
                    ]
                ]
            ],
            ['access-token' => $leadTradeSeeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'lead_id',
                    'type',
                    'make',
                    'model',
                    'year',
                    'price',
                    'length',
                    'width',
                    'notes',
                    'created_at',
                    // 'images' => [
                    //     '*' => [
                    //         'filename',
                    //         'path',
                    //     ]
                    // ]
                ]
            ]);
        
        $this->assertDatabaseHas('website_lead_trades', [
            'lead_id' => $leadTradeSeeder->lead->getKey(),
            'id' => $tradeId,
            'make' => self::TEST_VEHICLE_MAKE,
            'model' => self::TEST_VEHICLE_MODEL,
            'year' => self::TEST_VEHICLE_YEAR,
            'notes' => self::TEST_NOTE
        ]);

        $this->assertDatabaseMissing('website_lead_trade_image', [
            'id' => $leadTradeImageId1
        ]);
        Storage::disk('s3')->assertMissing($randomString1);

        $this->assertDatabaseHas('website_lead_trade_image', [
            'id' => $leadTradeImageId2,
            'trade_id' => $tradeId,
            'filename' => 'existing_image2.png',
            'path' => Storage::disk('s3')->url($randomString2)
        ]);
        Storage::disk('s3')->assertExists($randomString2);

        $this->assertDatabaseHas('website_lead_trade_image', [
            'trade_id' => $tradeId,
            'filename' => 'new_image3.png',
            'path' => Storage::disk('s3')->url($randomString3)
        ]);
        Storage::disk('s3')->assertExists($randomString3);

        // cleanup
        $leadTradeSeeder->cleanUp();
        Storage::disk('s3')->delete([
            $randomString2,
            $randomString3
        ]);
    }

    /**
     * @covers ::destroy
     * @group CRM
     */
    public function testDestroy()
    {
        $leadTradeSeeder = new LeadTradeSeeder();
        $leadTradeSeeder->seed();

        $tradeId = $leadTradeSeeder->leadTrades[0]['id'];
        $leadTradeImageId1 = $leadTradeSeeder->leadTrades[0]['images'][0]['id'];

        // prepare test data
        // update image 1 with real data
        $randomString1 = $this->faker->md5();
        Storage::disk('s3')->put($randomString1, '');
        LeadTradeImage::find($leadTradeImageId1)->update([
            'filename' => 'existing_image1.png',
            'path' => Storage::disk('s3')->url($randomString1)
        ]);
        // end prepare test data

        // confirm test data
        $this->assertDatabaseHas('website_lead_trade_image', [
            'id' => $leadTradeImageId1,
            'trade_id' => $tradeId,
            'filename' => 'existing_image1.png',
            'path' => Storage::disk('s3')->url($randomString1)
        ]);

        Storage::disk('s3')->assertExists($randomString1);
        // end confirm test data

        $response = $this->json(
            'DELETE',
            '/api/leads/'. $leadTradeSeeder->lead->getKey() .'/trades/' . $tradeId,
            [],
            ['access-token' => $leadTradeSeeder->authToken->access_token]
        );

        $response->assertStatus(200);

        $this->assertDatabaseMissing('website_lead_trades', [
            'id' => $tradeId
        ]);
        $this->assertDatabaseMissing('website_lead_trade_image', [
            'trade_id' => $tradeId
        ]);
        Storage::disk('s3')->assertMissing($randomString1);

        $leadTradeSeeder->cleanUp();
    }

    /**
     * @covers ::show
     * @group CRM
     */
    public function testShow()
    {
        $leadTradeSeeder = new LeadTradeSeeder();
        $leadTradeSeeder->seed();

        $tradeId = $leadTradeSeeder->leadTrades[0]['id'];

        $response = $this->json(
            'GET',
            '/api/leads/'. $leadTradeSeeder->lead->getKey() .'/trades/'. $tradeId .'?with=images',
            [],
            ['access-token' => $leadTradeSeeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'lead_id',
                    'type',
                    'make',
                    'model',
                    'year',
                    'price',
                    'length',
                    'width',
                    'notes',
                    'created_at',
                    // 'images' => [
                    //     '*' => [
                    //         'filename',
                    //         'path',
                    //     ]
                    // ]
                ]
            ]);



        $leadTradeSeeder->cleanUp();
    }
}
