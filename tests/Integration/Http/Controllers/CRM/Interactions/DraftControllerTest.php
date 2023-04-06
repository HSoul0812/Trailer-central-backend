<?php

namespace Tests\Integration\Http\Controllers\CRM\Interactions;

use Tests\Integration\IntegrationTestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\CRM\Interactions\InteractionSeeder;
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

class DraftControllerTest extends IntegrationTestCase {

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
    public function testGetEmailDraft()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];

        $response = $this->json(
            'GET',
            '/api/leads/'. $lead->getKey() .'/interactions/draft',
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
            '/api/leads/'. $lead->getKey() .'/interactions/draft',
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
            '/api/leads/'. $lead->getKey() .'/interactions/draft',
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
            '/api/leads/'. $lead->getKey() .'/interactions/draft/send',
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
            '/api/leads/'. $lead->getKey() .'/interactions/'. $interactionId .'/draft',
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