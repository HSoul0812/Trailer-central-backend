<?php

namespace Tests\Integration\Http\Controllers\CRM\Email;

use Tests\Integration\IntegrationTestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\CRM\Email\CampaignSeeder;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Models\User\NewDealerUser;

/**
 * Class CampaignControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Email
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Email\CampaignController
 */

class CampaignControllerTest extends IntegrationTestCase {
    use DatabaseTransactions;
    
    /**
     * @var CampaignSeeder
     */
    private $seeder;

    /**
     * @var string
     */
    private $accessToken;

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new CampaignSeeder();
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
            '/api/user/emailbuilder/campaign',
            [],
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'template_id',
                        'template',
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
                        'is_enabled',
                        'categories',
                        'brands',
                        'factory_campaign_id',
                        'approved',
                        'is_from_factory'
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
        foreach ($this->seeder->createdCampaigns as $campaign) {
            $expectedData[] = [
                'id' => (int)$campaign->drip_campaigns_id,
                'template_id' => (int)$campaign->email_template_id,
                'template' => $campaign->template->toArray(),
                'location_id' => (int)$campaign->location_id,
                'location' => $campaign->location,
                'send_after_days' => (int)$campaign->send_after_days,
                'action' => $campaign->action,
                'unit_category' => $campaign->unit_category,
                'campaign_name' => $campaign->campaign_name,
                'user_id' => (int)$campaign->user_id,
                'from_email_address' => $campaign->from_email_address,
                'campaign_subject' => $campaign->campaign_subject,
                'include_archived' => (int)$campaign->include_archived,
                'is_enabled' => (int)$campaign->is_enabled,
                'categories' => $campaign->categories->toArray(),
                'brands' => $campaign->brands->toArray(),
                'factory_campaign_id' => $campaign->factory ? $campaign->factory->id : null,
                'approved' => $campaign->factory ? $campaign->factory->is_approved : true,
                'is_from_factory' => isset($campaign->factory)
            ];
        }
        $this->assertResponseDataEquals($response, $expectedData, false);
    }
}