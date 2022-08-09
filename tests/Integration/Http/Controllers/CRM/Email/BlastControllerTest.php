<?php

namespace Tests\Integration\Http\Controllers\CRM\Email;

use App\Models\CRM\Email\Blast;
use App\Transformers\CRM\Email\BlastTransformer;
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
    use DatabaseTransactions;

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
        NewDealerUser::destroy($this->seeder->dealer->dealer_id);

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
        // Grab a template from the seeder
        $template = $this->seeder->template;

        // Initialize the required data for the blast
        $rawBlast = [
            'email_template_id' => $template->id,
            'campaign_name' => 'Test Campaign',
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
            $this->seeder->dealer->newDealerUser->user_id,
            $response['data']['user_id'],
            "The user doesn't match"
        );
        $this->assertSame($template->id, $response['data']['template_id'], "The template id doesn't match");

        // Corroborate the record was inserted in the database
        $this->assertDatabaseHas(Blast::getTableName(), [
            'email_blasts_id' => $response['data']['id']
        ]);
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdate()
    {
        // Grab a template and a blast from the seeder
        $template = $this->seeder->template;
        $blast = $this->seeder->createdBlasts[0];

        // Inivialize some data for the updated blast
        $updatedInfo = [
            'email_template_id' => $template->id,
            'campaign_name' => 'Updated Campaign',
            'send_date' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'action' => Blast::ACTION_CONTACTED,
            'send_after_days' => '2',
        ];

        // POST the data into de API
        $response = $this->json(
            'POST',
            self::API_ENDPOINT . '/' . $blast->email_blasts_id,
            $updatedInfo,
            ['access-token' => $this->accessToken]
        );

        // Check if we have a valid response from the API
        $response->assertStatus(200)
            ->assertJsonStructure(self::SINGLE_JSON_STRUCTURE);

        // Check the data was assigned correctly
        $this->assertSame(
            $this->seeder->dealer->newDealerUser->user_id,
            $response['data']['user_id'],
            "The user doesn't match"
        );
        $this->assertSame(
            $updatedInfo['campaign_name'],
            $response['data']['campaign_name'],
            "The blast campaign's name doesn't match"
        );

        // Corroborate the record was updated in the database
        $this->assertDatabaseHas(Blast::getTableName(), [
            'email_blasts_id' => $response['data']['id'],
            'campaign_name' => $updatedInfo['campaign_name']
        ]);
    }

    /**
     * @group CRM
     * @covers ::destroy
     */
    public function testDestroy()
    {
        // Grab a blast from the seeder
        $blast = $this->seeder->createdBlasts[0];

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
           'email_blasts_id' => $blast->id
        ]);
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
