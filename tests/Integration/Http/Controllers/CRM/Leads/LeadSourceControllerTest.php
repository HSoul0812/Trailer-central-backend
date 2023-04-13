<?php

namespace Tests\Integration\Http\Controllers\CRM\Leads;

use App\Models\CRM\Leads\LeadSource;
use Tests\database\seeds\CRM\Leads\StatusSeeder;
use Tests\Integration\IntegrationTestCase;
use Faker\Factory;
use App\Models\User\NewUser;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\User\User;
use App\Models\User\AuthToken;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;

/**
 * Class LeadSourceControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Leads
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Leads\LeadSourceController
 */
class LeadSourceControllerTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->dealer = factory(User::class)->create([
            'type' => User::TYPE_DEALER,
            'state' => User::STATUS_ACTIVE
        ]);

        /**
         * necessary data for CRM user
         */
        $user = factory(NewUser::class)->create();
        $newDealerUserRepo = app(NewDealerUserRepositoryInterface::class);
        $newDealerUser = $newDealerUserRepo->create([
            'user_id' => $user->user_id,
            'salt' => md5((string)$user->user_id), // random string
            'auto_import_hide' => 0,
            'auto_msrp' => 0
        ]);
        $this->dealer->newDealerUser()->save($newDealerUser);
        $crmUserRepo = app(CrmUserRepositoryInterface::class);
        $crmUserRepo->create([
            'user_id' => $user->user_id,
            'logo' => '',
            'first_name' => '',
            'last_name' => '',
            'display_name' => '',
            'dealer_name' => $this->dealer->name,
            'active' => 1
        ]);
        // END

        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->getKey(),
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $this->sources = factory(LeadSource::class, 5)->create([
            'user_id' => $user->user_id
        ]);
    }

    public function tearDown(): void
    {
        $userId = $this->dealer->newDealerUser->user_id;

        LeadSource::where('user_id', $userId)->delete();

        $this->token->delete();

        // Delete CRM User Related Data
        NewDealerUser::where(['user_id' => $userId])->delete();
        CrmUser::where(['user_id' => $userId])->delete();
        NewUser::destroy($userId);

        $this->dealer->delete();

        parent::tearDown();
    }

    /**
     * @group CRM
     * @covers ::index
     */
    public function testIndex()
    {
        $response = $this->json(
            'GET',
            '/api/leads/sources',
            [],
            ['access-token' => $this->token->access_token]
        );

        $content = json_decode($response->getContent(), true)['data'];

        $response->assertStatus(200)
            ->assertJsonStructure([
            'data' => [
                '*' => [
                    'lead_source_id',
                    'user_id',
                    'source_name',
                    'date_added',
                    'parent_id',
                    'deleted'
                ]
            ]
        ]);

        foreach ($content as $source) {

            $this->assertDatabaseHas(LeadSource::getTableName(), [
                'source_name' => $source['source_name']
            ]);
        }
    }

    /**
     * @group CRM
     * @covers ::create
     */
    public function testCreate()
    {
        $newSource = $this->faker->company();

        $response = $this->json(
            'PUT',
            '/api/leads/sources',
            [
                'source_name' => $newSource
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'lead_source_id',
                    'user_id',
                    'source_name',
                    'date_added',
                    'parent_id',
                    'deleted'
                ]
            ]);

        $source = json_decode($response->getContent(), true)['data'];

        $this->assertDatabaseHas(LeadSource::getTableName(), [
            'source_name' => $newSource,
            'user_id' => $source['user_id'],
            'deleted' => 0
        ]);
    }

    /**
     * @group CRM
     * @covers ::delete
     */
    public function testDelete()
    {
        $response = $this->json(
            'DELETE',
            '/api/leads/sources/'. $this->sources[0]->lead_source_id,
            [],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200)
            ->assertJson([
                'response' => [
                    'status' => 'success'
                ]
            ]);

        $this->assertDatabaseHas(LeadSource::getTableName(), [
            'lead_source_id' => $this->sources[0]->lead_source_id,
            'deleted' => 1
        ]);
    }

    /**
     * @group CRM
     */
    public function testMiddleware()
    {
        $nonExistingId = PHP_INT_MAX;

        $response = $this->json(
            'DELETE',
            '/api/leads/sources/'. $nonExistingId,
            [],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(422)
            ->assertJsonPath('errors.id.0', 'Lead Source does not exist.');
    }
}