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
    }
}