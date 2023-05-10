<?php

namespace Tests\Integration\Http\Controllers\CRM\Email;

use App\Models\CRM\Email\Template;
use Dingo\Api\Facade\API;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\Integration\IntegrationTestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\CRM\Email\TemplateSeeder;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Models\User\NewDealerUser;

/**
 * Class TemplateControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Email
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Email\TemplateController
 */

class TemplateControllerTest extends IntegrationTestCase
{
    use DatabaseTransactions, WithFaker;
    
    /**
     * @var TemplateSeeder
     */
    private $seeder;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private const API_ENDPOINT = '/api/user/emailbuilder/template';

    /**
     * @var string
     */
    private const TEMPLATE_FILE = "templates/versafix-1/template-versafix-1.html";

    /**
     * @var string[]
     */
    private const SINGLE_JSON_STRUCTURE = [
        'data' => [
            'id',
            'user_id',
            'key',
            'name',
            'created_at',
            'html',
            'template_metadata',
            'template_json'
        ]
    ];

    /**
     * @var string[]
     */
    private const PAGINATED_JSON_STRUCTURE = [
        'data' => [
            '*' => [
                'id',
                'user_id',
                'key',
                'name',
                'created_at',
                'html',
                'template_metadata',
                'template_json'
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

        $this->seeder = new TemplateSeeder();
        $this->seeder->seed();
        $this->accessToken = $this->seeder->dealer->access_token;

        // Fixing Invalid User Id
        $newDealerUserRepo = app(NewDealerUserRepositoryInterface::class);
        $newDealerUser = $newDealerUserRepo->create([
            'user_id' => $this->seeder->user->user_id,
            'salt' => md5((string)$this->seeder->user->user_id),
            'auto_import_hide' => 0,
            'auto_msrp' => 0
        ]);
        $this->seeder->dealer->newDealerUser()->save($newDealerUser);
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
        // Get data from the API
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
        foreach ($this->seeder->createdTemplates as $template) {
            $expectedData[] = $this->expectedDataFormat($template);
        }
        $this->assertResponseDataEquals($response, $expectedData, false);
    }

    /**
     * @group CRM
     * @covers ::show
     */
    public function testShow()
    {
        // Grab a template from the seeder
        $template = $this->seeder->createdTemplates[0];

        // Get the data from the API endpoint
        $response = $this->json(
            'GET',
            self::API_ENDPOINT . '/' . $template->id,
            [],
            ['access-token' => $this->accessToken]
        );

        // Check that we have a valid response from the API
        $response->assertStatus(200)
            ->assertJsonStructure(self::SINGLE_JSON_STRUCTURE);

        // Is the data in the expected format?
        $expectedData = $this->expectedDataFormat($template);
        $this->assertResponseDataEquals($response, $expectedData, false);
    }

    /**
     * @group CRM
     * @covers ::create
     */
    public function testCreate()
    {
        // Initialize the required data for the template
        $rawTemplate = [
            'name' => 'Create Email Template Test',
            'template' => self::TEMPLATE_FILE,
            'template_key' => Str::random(7)
        ];

        // PUT the data into the API
        $response = $this->json(
            'PUT',
            self::API_ENDPOINT,
            $rawTemplate,
            ['access-token' => $this->accessToken]
        );

        // Check if we have a valid response from the API
        $response->assertStatus(200)
            ->assertJsonStructure(self::SINGLE_JSON_STRUCTURE);

        // Check that the data was assigned correctly
        $this->assertSame($this->seeder->dealer->newDealerUser->user_id, $response['data']['user_id'], "The user doesn't match");
        $this->assertSame('Create Email Template Test', $response['data']['name'], "The template's name doesn't match");

        // Corroborate the record was inserted in the database
        $this->assertDatabaseHas(Template::getTableName(), [
            'id' => $response['data']['id'],
        ]);
    }

    /**
     * @group CRM
     * @covers ::create
     * @dataProvider badArgumentsProvider
     */
    public function testCreateWithBadArguments(
        array  $parameters,
        int    $expectedHttpStatusCode,
        string $expectedMessage,
        array  $expectedErrorMessages
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
        $this->assertSame($expectedMessage, $json['message']);
        $this->assertSame($expectedErrorMessages, $json['errors']);
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdate()
    {
        // Grab a template from the seeder
        $template = $this->seeder->createdTemplates[0];

        // Initialize some data for the updated template
        $updatedInfo = [
            "name" => "Updated Template",
        ];

        // POST the data into the API
        $response = $this->json(
            'POST',
            self::API_ENDPOINT . '/' . $template->id,
            $updatedInfo,
            ['access-token' => $this->accessToken]
        );

        // Check if we have a valid response from the API
        $response->assertStatus(200)
            ->assertJsonStructure(self::SINGLE_JSON_STRUCTURE);

        // Check that the data was assigned correctly
        $this->assertSame($this->seeder->dealer->newDealerUser->user_id, $response['data']['user_id'], "The user doesn't match");
        $this->assertSame($updatedInfo['name'], $response['data']['name'], "The template's name doesn't match");

        // Corroborate the record was updated in the database
        $this->assertDatabaseHas(Template::getTableName(), [
            'id' => $response['data']['id'],
            'name' => $updatedInfo['name']
        ]);
    }

    /**
     * @group CRM
     * @covers ::destroy
     */
    public function testDestroy()
    {
        // Grab a template from the seeder
        $template = $this->seeder->createdTemplates[0];

        // Send DELETE request to the API
        $response = $this->json(
            'DELETE',
            self::API_ENDPOINT . '/' . $template->id,
            [],
            ['access-token' => $this->accessToken]
        );

        // Check that the response is a no content response.
        $response->assertStatus(200);

        // Check The Correct Item Was Deleted
        $this->assertSame('success', $response['response']['status'], "The response status is not success");

        // Corroborate the record was deleted in the database
        $this->assertDatabaseMissing(Template::getTableName(), [
            'id' => $template->id
        ]);
    }

    /**
     * @param $template
     * @return array
     */
    private function expectedDataFormat($template): array
    {
        return [
            'id' => (int)$template->id,
            'user_id' => (int)$template->user_id,
            'name' => $template->name,
            'key' => $template->template_key,
            'custom' => $template->custom_template_name,
            'template' => [
               'key' => $template->template,
               'name' => $template->name,
               'metadata' => $template->template_metadata,
               'json' => $template->template_json,
            ], 
            'created_at' => (string)$template->date,
            'html' => $template->html,
            'template_metadata' => $template->template_metadata,
            'template_json' => $template->template_json
        ];
    }

    public function badArgumentsProvider(): array
    {
        $this->refreshApplication();
        $this->setUpTraits();

        return [
            'No template key' => [
                [
                    'name' => 'Test template',
                    'template' => self::TEMPLATE_FILE,
                ],
                422,
                'Validation Failed',
                [
                    'template_key' => ['The template key field is required.'],
                ]
            ],
            'Invalid data' => [
                [
                    'name' => 123,
                    'template' => 123,
                    'template_key' => 123,
                ],
                422,
                'Validation Failed',
                [
                    'name' => ['The name must be a string.'],
                    'template' => ['The template must be a string.'],
                    'template_key' => ['The template key must be a string.'],
                ]
            ]
        ];
    }
}
