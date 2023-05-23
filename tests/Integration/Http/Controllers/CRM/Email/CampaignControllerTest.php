<?php

namespace Tests\Integration\Http\Controllers\CRM\Email;

use App\Models\CRM\Email\Campaign;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Integration\IntegrationTestCase;
use Tests\database\seeds\CRM\Email\CampaignSeeder;

/**
 * Class CampaignControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Email
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Email\CampaignController
 */
class CampaignControllerTest extends IntegrationTestCase
{
    use WithFaker;

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
     *
     * @return void
     */
    public function testIndex()
    {
        $this->seeder->seed();

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

    /**
     * @group CRM
     * @covers ::show
     *
     * @return void
     */
    public function testShow()
    {
        $this->seeder->seed();

        /** @var Campaign $campaign */
        $campaign = $this->seeder->createdCampaigns[0];

        $response = $this->json(
            'GET',
            '/api/user/emailbuilder/campaign/' . $campaign->drip_campaigns_id,
            [],
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
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
            ]);

        $expectedData = [
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

        $this->assertResponseDataEquals($response, $expectedData, false);
    }

    /**
     * @group CRM
     * @covers ::show
     *
     * @return void
     */
    public function testShowReport()
    {
        $this->seeder->seed();

        /** @var Campaign $campaign */
        $campaign = $this->seeder->createdCampaigns[0];

        $totalAction = count($this->seeder->campaignsSent);

        $response = $this->json(
            'GET',
            '/api/user/emailbuilder/campaign/' . $campaign->drip_campaigns_id .'?include=report',
            [],
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
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
                    'is_from_factory',
                    'report' => [
                        'data' => [
                            'sent',
                            'delivered',
                            'bounced',
                            'complaints',
                            'unsubscribed',
                            'opened',
                            'clicked',
                            'skipped',
                            'failed',
                        ]
                    ],
                ]
            ])
            ->assertJsonPath('data.report.data.sent', $totalAction)
            ->assertJsonPath('data.report.data.delivered', $totalAction)
            ->assertJsonPath('data.report.data.bounced', $totalAction)
            ->assertJsonPath('data.report.data.complaints', $totalAction)
            ->assertJsonPath('data.report.data.unsubscribed', $totalAction)
            ->assertJsonPath('data.report.data.opened', $totalAction)
            ->assertJsonPath('data.report.data.clicked', $totalAction)
            ->assertJsonPath('data.report.data.skipped', $totalAction)
            ->assertJsonPath('data.report.data.failed', $totalAction);

        $expectedData = [
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

        $this->assertResponseDataEquals($response, $expectedData, false);
    }

    /**
     * @group CRM
     * @covers ::create
     *
     * @return void
     */
    public function testCreate()
    {
        $userId = $this->seeder->user->getKey();

        $this->assertDatabaseMissing('crm_drip_campaigns', ['user_id' => $userId]);

        $params = [
            'user_id' => $userId,
            'email_template_id' => $this->seeder->template->getKey(),
            'campaign_name' => $this->faker->unique()->word,
            'send_after_days' => $this->faker->unique()->randomDigit,
            'action' => 'inquired',
            'campaign_subject' => $this->faker->unique()->sentence,
            'include_archived' => 1,
            'from_email_address' => $this->faker->unique()->email,
            'is_enabled' => $this->faker->unique()->boolean,
        ];

        $response = $this->json(
            'PUT',
            '/api/user/emailbuilder/campaign',
            $params,
            ['access-token' => $this->accessToken]
        );

        $this->assertDatabaseHas('crm_drip_campaigns', $params);

        $params['template_id'] = $params['email_template_id'];
        unset($params['email_template_id']);

        $response->assertStatus(200);

        $this->assertResponseDataEquals($response, $params, false);
    }

    /**
     * @group CRM
     * @covers ::update
     *
     * @return void
     */
    public function testUpdate()
    {
        $this->seeder->seed();

        /** @var Campaign $campaign */
        $campaign = $this->seeder->createdCampaigns[0];

        $params = [
            'campaign_name' => $this->faker->unique()->word,
            'send_after_days' => $this->faker->unique()->randomDigit,
            'action' => 'inquired',
            'campaign_subject' => $this->faker->unique()->sentence,
            'include_archived' => 1,
            'from_email_address' => $this->faker->unique()->email,
            'is_enabled' => $this->faker->unique()->boolean,
        ];

        $response = $this->json(
            'POST',
            '/api/user/emailbuilder/campaign/' . $campaign->getKey(),
            $params,
            ['access-token' => $this->accessToken]
        );

        $params['drip_campaigns_id'] = $campaign->getKey();

        $this->assertDatabaseHas('crm_drip_campaigns', $params);

        $params['id'] = $params['drip_campaigns_id'];
        unset($params['drip_campaigns_id']);

        $response->assertStatus(200);

        $this->assertResponseDataEquals($response, $params, false);
    }

    /**
     * @group CRM
     * @covers ::destroy
     *
     * @return void
     */
    public function testDestroy()
    {
        $this->seeder->seed();

        /** @var Campaign $campaign */
        $campaign = $this->seeder->createdCampaigns[0];

        $this->assertDatabaseHas('crm_drip_campaigns', ['drip_campaigns_id' => $campaign->getKey()]);

        $response = $this->json(
            'DELETE',
            '/api/user/emailbuilder/campaign/' . $campaign->getKey(),
            [],
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('crm_drip_campaigns', ['drip_campaigns_id' => $campaign->getKey()]);
    }
}
