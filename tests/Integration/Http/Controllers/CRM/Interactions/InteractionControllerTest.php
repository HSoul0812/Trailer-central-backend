<?php

namespace Tests\Integration\Http\Controllers\CRM\Interactions;

use Tests\Integration\IntegrationTestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\CRM\Interactions\InteractionSeeder;
use App\Models\CRM\Interactions\Interaction;
use Carbon\Carbon;
use Faker\Factory as Faker;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;
use App\Models\CRM\Email\Attachment;
use App\Models\CRM\Interactions\InteractionEmail;
use App\Models\CRM\Interactions\EmailHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use App\Mail\CRM\Interactions\EmailBuilderEmail;
use App\Mail\CRM\CustomEmail;
use App\Services\CRM\Interactions\InteractionService;
use Mockery;

class InteractionControllerTest extends IntegrationTestCase {

    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker::create();
        
        $this->instanceMock('imageHelper', ImageHelper::class);
    }

    /**
     * @group CRM
     */
    public function testCreateContactInteractionWithEmptyNote()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');

        $response = $this->json(
            'PUT',
            '/api/leads/'. $lead->getKey() .'/interactions',
            [
                'interaction_type' => Interaction::TYPE_CONTACT,
                'interaction_time' => $interactionTime,
                'interaction_notes' => ''
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'type',
                    'time',
                    'notes',
                    'contact_name',
                    'username',
                    'to_no',
                    'interaction_time'
                ]
            ])
            ->assertJsonPath('data.type', Interaction::TYPE_CONTACT)
            ->assertJsonPath('data.interaction_time', $interactionTime);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testCreateNonContactInteractionWithEmptyNote()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');
        $interactionNote = md5(time());

        $response = $this->json(
            'PUT',
            '/api/leads/'. $lead->getKey() .'/interactions',
            [
                'interaction_type' => Interaction::TYPE_TASK,
                'interaction_time' => $interactionTime,
                'interaction_notes' => ''
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['interaction_notes']);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testCreateNonContactInteraction()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');
        $interactionNote = md5(time());

        $response = $this->json(
            'PUT',
            '/api/leads/'. $lead->getKey() .'/interactions',
            [
                'interaction_type' => Interaction::TYPE_TASK,
                'interaction_time' => $interactionTime,
                'interaction_notes' => $interactionNote
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'type',
                    'time',
                    'notes',
                    'contact_name',
                    'username',
                    'to_no',
                    'interaction_time'
                ]
            ])
            ->assertJsonPath('data.type', Interaction::TYPE_TASK)
            ->assertJsonPath('data.interaction_time', $interactionTime)
            ->assertJsonPath('data.notes', $interactionNote);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testUpdateContactInteraction()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interaction = $seeder->dealerInteractions[0];
        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');
        $interactionNote = md5(time());

        $response = $this->json(
            'POST',
            '/api/leads/'. $lead->getKey() .'/interactions/'. $interaction->getKey(),
            [
                'interaction_type' => Interaction::TYPE_CONTACT,
                'interaction_time' => $interactionTime,
                'interaction_notes' => ''
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'type',
                    'time',
                    'notes',
                    'contact_name',
                    'username',
                    'to_no',
                    'interaction_time'
                ]
            ])
            ->assertJsonPath('data.type', Interaction::TYPE_CONTACT)
            ->assertJsonPath('data.interaction_time', $interactionTime)
            ->assertJsonPath('data.notes', '');

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testUpdateNonContactInteractionWithEmptyNote()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interaction = $seeder->dealerInteractions[0];
        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');

        $response = $this->json(
            'POST',
            '/api/leads/'. $lead->getKey() .'/interactions/'. $interaction->getKey(),
            [
                'interaction_type' => Interaction::TYPE_TASK,
                'interaction_time' => $interactionTime,
                'interaction_notes' => ''
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['interaction_notes']);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testUpdateNonContactInteractionWithNote()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interaction = $seeder->dealerInteractions[0];

        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');
        $interactionNote = md5(time());

        $response = $this->json(
            'POST',
            '/api/leads/'. $lead->getKey() .'/interactions/'. $interaction->getKey(),
            [
                'interaction_type' => Interaction::TYPE_TASK,
                'interaction_time' => $interactionTime,
                'interaction_notes' => $interactionNote
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'type',
                    'time',
                    'notes',
                    'contact_name',
                    'username',
                    'to_no',
                    'interaction_time'
                ]
            ])
            ->assertJsonPath('data.type', Interaction::TYPE_TASK)
            ->assertJsonPath('data.interaction_time', $interactionTime)
            ->assertJsonPath('data.notes', $interactionNote);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testCloseInteraction()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interaction = $seeder->dealerInteractions[0];

        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');
        $interactionNote = md5(time());

        $response = $this->json(
            'POST',
            '/api/leads/'. $lead->getKey() .'/interactions/'. $interaction->getKey(),
            [
                'is_closed' => 1
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors();

        $response = $this->json(
            'GET',
            '/api/user/interactions/tasks',
            [],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingExact(['data.*.id' => $interaction->getKey()]);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testGetEmailDraft()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];

        $response = $this->json(
            'GET',
            '/api/leads/'. $lead->getKey() .'/interactions/draft-email',
            [],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'subject',
                    'body',
                    'from_email',
                    'from_name',
                    'to_email',
                    'to_name',
                    'replyto_email',
                    'replyto_name'
                ]
            ]);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     * 
     * Test: 
     *  - Save Email Draft
     *  - Save Email Draft with Existing Attachment
     *  - Save and Send Email Draft
     *  - Get Email Draft to Reply Interaction
     */
    public function testSaveEmailDraft()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $emailSubject = $this->faker->md5();
        $emailBody = $this->faker->md5();
        $fileName = $this->faker->md5() .'.pdf';

        // mock getRandomString()
        $randomString = $this->faker->md5();
        $this->imageHelper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRandomString')
            ->once()
            ->andReturn($randomString);

        $response = $this->json(
            'POST',
            '/api/leads/'. $lead->getKey() .'/interactions/draft-email',
            [
                'subject' => $emailSubject,
                'body' => $emailBody,
                'new_attachments' => [
                    UploadedFile::fake()->create($fileName)->size(1000)
                ]
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200) 
            ->assertJsonStructure([
                'data' => [
                    'subject',
                    'body',
                    'from_email',
                    'from_name',
                    'to_email',
                    'to_name',
                    'replyto_email',
                    'replyto_name',
                    'attachments' => [
                        '*' => [
                            'filename',
                            'original_filename'
                        ]
                    ]
                ]
            ]);

        $content = json_decode($response->getContent(), true)['data'];

        $this->assertDatabaseHas('crm_email_history', [
            'lead_id' => $lead->getKey(),
            'subject' => $emailSubject,
            'body' => $emailBody
        ]);

        $this->assertDatabaseHas('crm_email_attachments', [
            'original_filename' => $fileName
        ]);

        Storage::disk('s3')->assertExists($randomString);

        // test Update Attachments

        // mock getRandomString()
        $randomString2 = $this->faker->md5();
        $this->imageHelper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRandomString')
            ->once()
            ->andReturn($randomString2);

        $anotherFileName = $this->faker->md5() .'.pdf';

        $response = $this->json(
            'POST',
            '/api/leads/'. $lead->getKey() .'/interactions/draft-email',
            [
                'subject' => $emailSubject,
                'body' => $emailBody,
                'new_attachments' => [
                    UploadedFile::fake()->create($anotherFileName)->size(1000)
                ],
                'existing_attachments' => $content['attachments']
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200);

        $content = json_decode($response->getContent(), true)['data'];

        // confirm that new & existing attachments still there
        
        Storage::disk('s3')->assertExists([
            $randomString, 
            $randomString2
        ]);

        $this->assertDatabaseHas('crm_email_attachments', [
            'original_filename' => $fileName
        ]);

        $this->assertDatabaseHas('crm_email_attachments', [
            'original_filename' => $anotherFileName
        ]);

        // test Send Email Draft

        $response = $this->json(
            'POST',
            '/api/leads/'. $lead->getKey() .'/interactions/send-email-draft',
            [
                'subject' => $emailSubject,
                'body' => $emailBody,
                'existing_attachments' => $content['attachments']
            ],
            ['access-token' => $seeder->authToken->access_token]
        );
        
        $response->assertStatus(200);

        $content = json_decode($response->getContent(), true)['data'];

        $interactionId = $content['id'];

        // test Get Email Draft to Reply Interaction

        $response = $this->json(
            'GET',
            '/api/leads/'. $lead->getKey() .'/interactions/'. $interactionId .'/reply-email',
            [],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'subject',
                    'body',
                    'from_email',
                    'from_name',
                    'to_email',
                    'to_name',
                    'replyto_email',
                    'replyto_name'
                ]
            ]);

        $content = json_decode($response->getContent(), true)['data'];

        $this->assertEquals($content['subject'], 'RE: '.$emailSubject);

        $seeder->cleanup();
    }
}