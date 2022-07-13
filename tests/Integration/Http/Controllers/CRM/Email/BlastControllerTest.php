<?php

namespace Tests\Integration\Http\Controllers\CRM\Email;

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

class BlastControllerTest extends IntegrationTestCase {
    use DatabaseTransactions;
    
    /**
     * @var BlastSeeder
     */
    private $seeder;

    /**
     * @var string
     */
    private $accessToken;

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

    public function testIndex()
    {
        $response = $this->json(
            'GET',
            '/api/user/emailbuilder/blast',
            [],
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
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
            ]);

        $expectedData = [];
        foreach ($this->seeder->createdBlasts as $blast) {
            $expectedData[] = [
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
        $this->assertResponseDataEquals($response, $expectedData, false);
    }
}