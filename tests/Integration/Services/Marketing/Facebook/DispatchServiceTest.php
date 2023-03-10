<?php

namespace Tests\Integration\Services\Marketing\Facebook;

use App\Models\Marketing\Facebook\Error;
use App\Models\Marketing\Facebook\Listings;
use App\Models\Marketing\Facebook\Marketplace;
use App\Models\User\NewDealerUser;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\Marketing\Facebook\MarketplaceSeeder;
use Tests\Integration\IntegrationTestCase;

/**
 * Class MarketingService
 * @package Tests\Integration\Services\Marketing\Facebook
 *
 * @coversDefaultClass \App\Services\Dispatch\Facebook\MarketplaceService
 */
class DispatchServiceTest extends IntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @var MarketplaceSeeder
     */
    private $seeder;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @const string
     */
    const API_BASE_URL = '/api/dispatch/facebook';

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new MarketplaceSeeder();
        $this->seeder->seed();
        $this->accessToken = $this->login();
    }

    public function tearDown(): void
    {
        NewDealerUser::destroy($this->seeder->dealer->dealer_id);

        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @group Marketing
     * @covers ::login
     */
    public function login(): string
    {
        $response = $this->json(
            'POST',
            self::API_BASE_URL,
            [
                'ip_address' => '10.1.1.62',
                'client_uuid' => 'fbm6829210710542',
                'version' => '0.0.1'
            ],
            []
        );
        $response->assertStatus(200)->assertJsonStructure(['data']);
        return $response['data'];
    }

    /**
     * @group Marketing
     * @covers ::dealer
     */
    public function testDealer()
    {
        $marketplace = $this->seeder->createdMarketplaces[0];

        $response = $this->json(
            'GET',
            self::API_BASE_URL . '/' . $marketplace->id,
            [],
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'locationId',
                    'name',
                    'integration',
                    'fb' => [
                        'username',
                        'password'
                    ],
                    'auth' => [
                        'type',
                        'username',
                        'password'
                    ],
                    'tunnels',
                    'inventory' => [
                        "type",
                        "page",
                        "pages",
                        "count",
                        "total",
                        "per_page",
                        "inventory"
                    ]
                ]
            ]);
    }

    /**
     * Test a listing is created correctly.
     *
     * @group Marketing
     * @covers ::create
     */
    public function testCreate()
    {
        // Grab an integration instance
        $marketplace = $this->seeder->createdMarketplaces[0];
        $inventory = $this->seeder->takeInventory();

        // Grab some images to send to the API
        $images = collect();
        for ($i = 1; $i < 4; $i++) {
            $newImage = $this->seeder->takeImage();
            if (isset($newImage)) {
                $images->push($newImage);
            }
        }

        $this->assertNotEmpty($images->toArray());

        // Create required parameters array
        $requiredParams = [
            'marketplace_id' => $marketplace->getKey(),
            'inventory_id' => $inventory->getKey(),
            'facebook_id' => $inventory->getKey(),
            'status' => Marketplace::STATUS_ACTIVE,
            'images' => $images->map(function ($image) {
                return $image->image_id;
            }),
            'username' => $marketplace->fb_username
        ];

        // Post to the API
        $response = $this->json(
            'POST',
            self::API_BASE_URL . '/' . $marketplace->id,
            $requiredParams,
            ['access-token' => $this->accessToken]
        );

        // Assert the response is valid
        $response->assertStatus(200);

        // Assert we have the minimum required structure
        $response->assertJsonStructure([
            'data' => [
                'id',
                'marketplace' => [
                    'id',
                    'dealer' => [
                        'id',
                        'identifier',
                        'name'
                    ],
                    'fb_username'
                ],
                'inventory' => [
                    'id',
                    'identifier',
                    'dealer' => [
                        'id',
                        'name'
                    ],
                    'dealer_location' => [
                        'id',
                        'name'
                    ]
                ],
                'facebook_id',
                'images' => [
                    'data' => [
                        '*' => [
                            'id',
                            'file'
                        ]
                    ]
                ]
            ]
        ]);

        // Add the ID to avoid similar results to fail the test without reasons
        $requiredParams['id'] = $response['data']['id'];
        // Rather than images, we are interested in the listing existing, so we can report ir correctly.
        unset($requiredParams['images']);

        // Assert the data was inserted to the database correctly
        $this->assertDatabaseHas(Listings::getTableName(), $requiredParams);

        // Assert the view has the correct data
        $this->assertDatabaseHas('dealer_fbm_overview', [
            'id' => $marketplace->getKey(),
            'units_posted_today' => "{$inventory->stock}"
        ]);
    }

    /**
     * @group Marketing
     * @covers ::step
     */
    public function testStep()
    {
        // Initialize required vars
        $marketplace = $this->seeder->createdMarketplaces[0];
        $inventory = $this->seeder->takeInventory();
        $errorMessage = "Missing data on post, cannot save post, skipping item and processing next!";
        $logs = [
            [
                "date" => now(),
                "logMessage" => $errorMessage,
                "loggerName" => "Logger name?",
                "loggerType" => "error"
            ]
        ];

        // Get the required params
        $requiredParams = [
            'step' => 'failed-post',
            'logs' => json_encode($logs),
            'action' => 'error',
            'inventory_id' => $inventory->getKey(),
            'error' => 'failed-post',
            'message' => $errorMessage
        ];

        // Post the step to the API
        $response = $this->json(
            'PUT',
            self::API_BASE_URL . '/' . $marketplace->id,
            $requiredParams,
            ['access-token' => $this->accessToken]
        );

        // Assert we have a valid response
        $response->assertStatus(200);

        // Assert we have a valid structure
        $response->assertJsonStructure([
            "data" => [
                "step",
                "action",
                "inventory_id",
                "status"
            ]
        ]);

        // Check that the database inserted the correct info
        $this->assertDatabaseHas(Error::getTableName(), [
            'marketplace_id' => $marketplace->getKey(),
            'inventory_id' => $inventory->getKey(),
            'action' => $requiredParams['action'],
            'error_type' => $requiredParams['step'],
            'error_message' => $errorMessage,
            'dismissed' => 0
        ]);

        // Check that the view has the correct info
        $this->assertDatabaseHas('dealer_fbm_overview', [
            'id' => $marketplace->getKey(),
            [ 'last_known_error_message', 'LIKE', "{$requiredParams['message']}" ],
            'error_today' => $requiredParams['message'],
        ]);
    }

    /**
     * Test errors are being reported correctly
     *
     * @group Marketing
     * @covers ::step
     * @dataProvider stepErrorsDataProvider
     *
     * @param array $parameters
     * @return void
     */
    public function testStepErrorsWithArguments(
        array $parameters
    ) {
        // Initialize required vars
        $marketplace = $this->seeder->createdMarketplaces[0];
        $inventory = $this->seeder->takeInventory();
        $errorMessage = $parameters['errorMessage'];
        $logs = [
            [
                "date" => now(),
                "logMessage" => $errorMessage,
                "loggerName" => "Logger name?",
                "loggerType" => $parameters['loggerType']
            ]
        ];

        // Get the required params
        $requiredParams = [
            'step' => $parameters['step'],
            'logs' => json_encode($logs),
            'action' => $parameters['action'],
            'inventory_id' => $inventory->getKey(),
            'error' => $parameters['error'],
            'message' => $errorMessage
        ];

        // Post the step to the API
        $response = $this->json(
            'PUT',
            self::API_BASE_URL . '/' . $marketplace->id,
            $requiredParams,
            ['access-token' => $this->accessToken]
        );

        // Assert we have a valid response
        $response->assertStatus(200);

        // Assert we have a valid structure
        $response->assertJsonStructure([
            "data" => [
                "step",
                "action",
                "inventory_id",
                "status"
            ]
        ]);

        // Check that the database inserted the correct info
        $this->assertDatabaseHas(Error::getTableName(), [
            'marketplace_id' => $marketplace->getKey(),
            'inventory_id' => $inventory->getKey(),
            'action' => $requiredParams['action'],
            'error_type' => $requiredParams['step'],
            'error_message' => $errorMessage,
            'dismissed' => 0
        ]);

        // Check that the view has the correct info
        $this->assertDatabaseHas('dealer_fbm_overview', [
            'id' => $marketplace->getKey(),
            [ 'last_known_error_message', 'LIKE', "{$requiredParams['message']}" ],
            'error_today' => $requiredParams['message'],
        ]);
    }

    /**
     * Data provider for step errors
     *
     * @return array[]
     */
    public function stepErrorsDataProvider(): array
    {
        $this->refreshApplication();
        $this->setUpTraits();

        return [
            'Missing data in post' => [
                [
                    'errorMessage' => 'Missing data on post, cannot save post, skipping item and processing next!',
                    'loggerType' => 'error',
                    'step' => 'failed-post',
                    'action' => 'error',
                    'error' => 'failed-post',
                ]
            ],
            'No tunnel active' => [
                [
                    'errorMessage' => 'No tunnel found active!',
                    'loggerType' => 'error',
                    'step' => 'missing-tunnel',
                    'action' => 'error',
                    'error' => 'missing-tunnel'
                ]
            ],
            'No tunnel installed' => [
                [
                    'errorMessage' => 'No tunnel is installed!',
                    'loggerType' => 'error',
                    'step' => 'missing-tunnel',
                    'action' => 'error',
                    'error' => 'missing-tunnel',
                ]
            ],
            'Marketplace inaccessible' => [
                [
                    'errorMessage' => 'Access to Marketplace is not available at this time. It may be due to your country of origin or your account being too new.',
                    'loggerType' => 'error',
                    'step' => 'marketplace-inaccessible',
                    'action' => 'error',
                    'error' => 'marketplace-inaccessible',
                ]
            ],
            'Marketplace blocked' => [
                [
                    'errorMessage' => 'Access to Marketplace blocked due to community standards. A review was requested. Please be more careful about what inventory gets sent to the marketplace and ensure it follows Facebook\'s Community Standards.',
                    'loggerType' => 'error',
                    'step' => 'marketplace-blocked',
                    'action' => 'error',
                    'error' => 'marketplace-blocked',
                ]
            ],
        ];
    }
}
