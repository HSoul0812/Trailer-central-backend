<?php

namespace Tests\Integration\Http\Controllers\CRM\Email;

use App\Models\CRM\Email\Blast;
use App\Transformers\CRM\Email\BlastTransformer;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\Integration\IntegrationTestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\CRM\Email\BlastSeeder;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Models\User\NewDealerUser;

/**
 * Class BlastControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Email
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Email\BlastController
 */
class BlastControllerTest extends IntegrationTestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     * @var BlastSeeder
     */
    private $seeder;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private const API_ENDPOINT = '/api/user/emailbuilder/blast';

    /**
     * @var array
     */
    private const SINGLE_JSON_STRUCTURE = [
        'data' => [
            'id',
            'template_id',
            'location_id',
            'location',
            'send_after_days',
            'action',
            'unit_category',
            'campaign_name',
            'user_id',
            'from_email_address',
            'campaign_subject',
            'include_archived',
            'send_date',
            'delivered',
            'cancelled',
            'categories',
            'brands',
            'total_sent',
            'factory_campaign_id',
            'approved'
        ]
    ];

    /**
     * @var array
     */
    private const PAGINATED_JSON_STRUCTURE = [
        'data' => [
            '*' => [
                'id',
                'template_id',
                'location_id',
                'location',
                'send_after_days',
                'action',
                'unit_category',
                'campaign_name',
                'user_id',
                'from_email_address',
                'campaign_subject',
                'include_archived',
                'send_date',
                'delivered',
                'cancelled',
                'categories',
                'brands',
                'total_sent',
                'factory_campaign_id',
                'approved'
            ]
        ],
        'meta' => [
            'pagination' => [
                'total',
                'count',
                'per_page',
                'current_page',
                'total_pages'
            ]
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new BlastSeeder();
        $this->seeder->seed();
        $this->accessToken = $this->seeder->dealer->access_token;
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @group CRM
     * @covers ::index
     */
    public function testIndex()
    {
        // Get the data from the API
        $response = $this->json(
            'GET',
            self::API_ENDPOINT,
            [],
            ['access-token' => $this->accessToken]
        );

        // Check that we have a valid response from the API
        $response->assertStatus(200)
            ->assertJsonStructure(self::PAGINATED_JSON_STRUCTURE);

        // Is the data in the expected format?
        $expectedData = [];
        foreach ($this->seeder->createdBlasts as $blast) {
            $expectedData[] = $this->expectedDataFormat($blast);
        }
        $this->assertResponseDataEquals($response, $expectedData, false);
    }

    /**
     * @group CRM
     * @covers ::show
     */
    public function testShow()
    {
        // Get a blast from the seeder
        $blast = $this->seeder->createdBlasts[0];

        // Get the data from the API endpoint
        $response = $this->json(
            'GET',
            self::API_ENDPOINT . '/' . $blast->email_blasts_id,
            [],
            ['access-token' => $this->accessToken]
        );

        // Check that we have a valid response from the API
        $response->assertStatus(200)
            ->assertJsonStructure(self::SINGLE_JSON_STRUCTURE);

        // Is the data in the expected format?
        $expectedData = $this->expectedDataFormat($blast);
        $this->assertResponseDataEquals($response, $expectedData, false);
    }

    /**
     * @group CRM
     * @covers ::create
     */
    public function testCreate()
    {
        $userId = $this->seeder->user->getKey();
        // Grab a template from the seeder
        $template = $this->seeder->template;

        // Initialize the required data for the blast
        $rawBlast = [
            'user_id' => $userId,
            'email_template_id' => $template->id,
            'campaign_name' => 'Test Campaign',
            'campaign_subject' => 'Test Campaign',
            'send_date' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'action' => Blast::ACTION_UNCONTACTED,
            'send_after_days' => '3',
        ];

        // PUT the data into the API
        $response = $this->json(
            'PUT',
            self::API_ENDPOINT,
            $rawBlast,
            ['access-token' => $this->accessToken]
        );

        // Check if we have a valid response from the API
        $response->assertStatus(200)
            ->assertJsonStructure(self::SINGLE_JSON_STRUCTURE);

        // Check that the data was assigned correctly
        $this->assertSame(
            $this->seeder->user->user_id,
            $response['data']['user_id'],
            "The user doesn't match"
        );
        $this->assertSame($template->id, $response['data']['template_id'], "The template id doesn't match");

        // Corroborate the record was inserted correctly in the database
        $this->assertDatabaseHas(Blast::getTableName(), [
            'email_blasts_id' => $response['data']['id'],
            'campaign_name' => $rawBlast['campaign_name'],
            'email_template_id' => $rawBlast['email_template_id'],
            'action' => $rawBlast['action'],
            'user_id' => $rawBlast['user_id'],
        ]);
    }

    /**
     * @group CRM
     * @covers ::create
     * @dataProvider badArgumentsProvider
     */
    public function testCreateWithBadArguments(
        array $parameters,
        int $expectedHttpStatusCode,
        string $expectedMessage,
        array $expectedErrorMessages
    ) {
        // PUT the data into the API
        $response = $this->json(
            'PUT',
            self::API_ENDPOINT,
            $parameters,
            ['access-token' => $this->accessToken]
        );

        // Check if we got the expected response from the API
        $response->assertStatus($expectedHttpStatusCode);
        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertEquals($expectedMessage, $json['message']);
        $this->assertEquals($expectedErrorMessages, $json['errors']);
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdate()
    {
        // Grab a blast from the seeder
        $blast = $this->seeder->createdBlasts[0];
        $randomDigit = $this->faker->unique()->randomDigit;

        // Initialize some data for the updated blast
        $updatedInfo = [
            'user_id' => $this->seeder->user->getKey(),
            'email_template_id' => $this->seeder->template->id,
            'campaign_name' => $this->faker->unique()->word,
            'send_date' => now()->addDays($randomDigit)->format('Y-m-d H:i:s'),
            'action' => Blast::ACTION_CONTACTED,
            'send_after_days' => $randomDigit,
            'unit_category' => null,
            'location_id' => null,
            'from_email_address' => $this->faker->email,
            'delivered' => 0,
            'cancelled' => 0,
            'campaign_subject' => $this->faker->sentence(5),
            'include_archived' => 1,
        ];

        // POST the data into de API
        $response = $this->json(
            'POST',
            self::API_ENDPOINT . '/' . $blast->email_blasts_id,
            $updatedInfo,
            ['access-token' => $this->accessToken]
        );

        // Add the ID for validation
        $updatedInfo['email_blasts_id'] = $blast->email_blasts_id;

        // Check we are getting a date and then remove it to avoid mismatches due to API times
        $this->assertNotFalse(strtotime($response['data']['send_date']));
        unset($updatedInfo['send_date']);

        // Check if we have a valid response from the API
        $response->assertStatus(200)
            ->assertJsonStructure(self::SINGLE_JSON_STRUCTURE);

        // Corroborate the record was updated in the database
        $this->assertDatabaseHas(Blast::getTableName(), $updatedInfo);

        // Update the info sent to match the transformer
        $updatedInfo['template_id'] = $updatedInfo['email_template_id'];
        $updatedInfo['id'] = $updatedInfo['email_blasts_id'];
        unset($updatedInfo['email_template_id']);
        unset($updatedInfo['email_blasts_id']);
        $updatedInfo['action'] = strtoupper($updatedInfo['action']);

        $this->assertResponseDataEquals($response, $updatedInfo, false);
    }

    /**
     * @group CRM
     * @covers ::destroy
     */
    public function testDestroy()
    {
        // Grab a blast from the seeder
        $blast = $this->seeder->createdBlasts[0];

        $this->assertDatabaseHas(Blast::getTableName(), [
            'email_blasts_id' => $blast->email_blasts_id
        ]);

        // Send a DELETE request to the API
        $response = $this->json(
            'DELETE',
            self::API_ENDPOINT . '/' . $blast->email_blasts_id,
            [],
            ['access-token' => $this->accessToken]
        );

        // Check that the response is a no content response
        $response->assertStatus(204);

        // Corroborate the record was deleted in the database
        $this->assertDatabaseMissing(Blast::getTableName(), [
            'email_blasts_id' => $blast->email_blasts_id
        ]);
    }

    public function badArgumentsProvider(): array
    {
        $this->refreshApplication();
        $this->setUpTraits();

        return [
            'Not enought data' => [
                [
                    'name' => 'Test template',
                ],
                422,
                'Validation Failed',
                [
                    'email_template_id' => ['The email template id field is required.'],
                    'campaign_name' => ['The blast name field is required.'],
                    'campaign_subject' => ['The blast subject field is required.'],
                    'send_date' => ['The send date field is required.'],
                    'action' => ['The action field is required.'],
                    'send_after_days' => ['The send after days field is required.'],
                ]
            ],
            'Invalid data' => [
                [
                    'email_template_id' => null,
                    'campaign_name' => 123,
                    'campaign_subject' => 123,
                    'send_date' => '1969-30-30',
                    'action' => Blast::ACTION_UNCONTACTED,
                    'send_after_days' => 1,
                ],
                422,
                'Validation Failed',
                [
                    'email_template_id' => ['The email template id field is required.'],
                    'campaign_name' => ['The blast name must be a string.'],
                    'campaign_subject' => ['The blast subject must be a string.'],
                    'send_date' => ['The send date does not match the format Y-m-d H:i:s.'],
                ]
            ]
        ];
    }

    private function expectedDataFormat($blast): array
    {
        return [
            'id' => (int)$blast->email_blasts_id,
            'template_id' => (int)$blast->email_template_id,
            'location_id' => $blast->location_id,
            'location' => $blast->location,
            'send_after_days' => (int)$blast->send_after_days,
            'action' => strtoupper($blast->action),
            'unit_category' => $blast->unit_category,
            'categories' => $blast->categories->toArray(),
            'brands' => $blast->brands->toArray(),
            'campaign_name' => $blast->campaign_name,
            'user_id' => (int)$blast->user_id,
            'from_email_address' => $blast->from_email_address,
            'campaign_subject' => $blast->campaign_subject,
            'include_archived' => $blast->include_archived,
            'send_date' => $blast->send_date,
            'delivered' => $blast->delivered,
            'cancelled' => $blast->cancelled,
            'total_sent' => $blast->sents()->count(),
            'factory_campaign_id' => $blast->factory ? $blast->factory->id : null,
            'approved' => $blast->factory ? $blast->factory->is_approved : true,
        ];
    }
}
