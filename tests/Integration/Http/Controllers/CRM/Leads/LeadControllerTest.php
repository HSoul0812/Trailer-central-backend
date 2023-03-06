<?php

namespace Tests\Integration\Http\Controllers\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\User\User;
use App\Models\User\AuthToken;
use Tests\Integration\IntegrationTestCase;
use App\Models\Website\Website;
use App\Models\User\NewUser;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Models\CRM\Interactions\Interaction;
use Carbon\Carbon;
use App\Models\CRM\Leads\LeadType;
use Faker\Factory;
use App\Models\CRM\User\Customer;
use App\Models\User\DealerLocation;
use App\Models\Inventory\Inventory;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\User\DealerUser;
use App\Models\User\DealerUserPermission;
use App\Models\User\Interfaces\PermissionsInterface;
use App\Nova\Permission;
use App\Models\CRM\User\SalesPerson;

/**
 * Class LeadControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Leads
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Leads\LeadController
 */
class LeadControllerTest extends IntegrationTestCase
{
    /** @var DealerUser */
    protected $dealer;

    /** @var Lead */
    protected $lead;

    /** @var AuthToken */
    protected $token;

    /** @var Website */
    protected $website;

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

        $this->website = factory(Website::class)->create([
            'dealer_id' => $this->dealer->getKey()
        ]);

        $this->lead = factory(Lead::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'website_id' => $this->website->getKey()
        ]);

        $this->leads = factory(Lead::class, 3)->create([
            'dealer_id' => $this->dealer->getKey(),
            'website_id' => $this->website->getKey()
        ]);
        
        // create an interaction for each lead

        $userId = $this->dealer->newDealerUser->user_id;
        $this->leads->each(function($lead) use ($userId) {
            factory(Interaction::class)->create([
                'tc_lead_id' => $lead->getKey(),
                'user_id' => $userId,
                'interaction_type' => Interaction::TYPE_TASK
            ]);
        });

        $interactionTime = Carbon::now()->addDays(2)->format('Y-m-d H:i:s');
        factory(Interaction::class)->create([
            'tc_lead_id' => $this->lead->getKey(),
            'user_id' => $userId,
            'interaction_type' => Interaction::TYPE_TASK,
            'interaction_time' => $interactionTime,
            'interaction_notes' => 'INTERACTION_NOTE'
        ]);

        factory(LeadStatus::class)->create([
            'tc_lead_identifier' => $this->lead->getKey(),
            'status' => LeadStatus::STATUS_UNCONTACTED,
            'contact_type' => LeadStatus::TYPE_TASK,
            'next_contact_date' => $interactionTime
        ]);

        // create Customer
        $this->customer = factory(Customer::class)->create([
            'first_name' => $this->lead->first_name,
            'last_name' => $this->lead->last_name,
            'email' => $this->lead->email_address,
            'home_phone' => $this->lead->phone_number,
            'work_phone' => $this->lead->phone_number,
            'cell_phone' => $this->lead->phone_number,
            'dealer_id' => $this->lead->dealer_id,
            'website_lead_id' => $this->lead->getKey()
        ]);

        $this->location = factory(DealerLocation::class)->create([
            'dealer_id' => $this->dealer->getKey(),
        ]);

        $this->inventories = factory(Inventory::class, 6)->create([
            'dealer_id' => $this->dealer->getKey(),
            'dealer_location_id' => $this->location->getKey()
        ]);

        // assign first 4 inventories as units of interest
        $this->lead->units()->saveMany($this->inventories->slice(0, 4));

        // create a Secondary User with Salesperson Role for CRM
        $this->salesPerson = factory(SalesPerson::class)->create([
            'user_id' => $user->user_id,
            'dealer_location_id' => $this->location->getKey()
        ]);

        $dealerUser = factory(DealerUser::class)->create([
            'dealer_id' => $this->dealer->getKey(),
        ]);

        factory(DealerUserPermission::class)->create([
            'dealer_user_id' => $dealerUser->dealer_user_id,
            'feature' => PermissionsInterface::CRM,
            'permission_level' => $this->salesPerson->getKey(),
        ]);
        
        $this->salespersonToken = factory(AuthToken::class)->create([
            'user_id' => $dealerUser->dealer_user_id,
            'user_type' => AuthToken::USER_TYPE_DEALER_USER,
            'access_token' => md5($dealerUser->dealer_user_id.uniqid())
        ]);
    }

    /**
     * @group CRM
     * @covers ::create
     */
    public function testCreateAsSalesperson()
    {
        $params = [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'lead_types' => $this->faker->randomElements(LeadType::TYPE_ARRAY, 2)
        ];

        $response = $this->json(
            'PUT',
            '/api/leads',
            $params,
            ['access-token' => $this->salespersonToken->access_token]
        );

        $response->assertStatus(200);

        $content = json_decode($response->getContent(), true);
        $leadId = $content['data']['id'];

        $this->assertDatabaseHas(Lead::getTableName(), [
            'identifier' => $leadId,
            'dealer_id' => $this->dealer->getKey()
        ]);

        $this->assertDatabaseHas(LeadStatus::getTableName(), [
            'sales_person_id' => $this->salesPerson->getKey(),
            'tc_lead_identifier' => $leadId
        ]);

        // cleanup
        LeadStatus::where('tc_lead_identifier', $leadId)->delete();
    }

    /**
     * @group CRM
     * @covers ::create
     */
    public function testCreateWithExistingCustomer()
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $params = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'lead_types' => $this->faker->randomElements(LeadType::TYPE_ARRAY, 2),
            'customer_id' => $this->customer->getKey()
        ];

        $this->assertDatabaseHas(Customer::getTableName(), [
            'id' => $this->customer->getKey(),
            'dealer_id' => $this->dealer->getKey(),
            'website_lead_id' => $this->lead->getKey()
        ]);

        $response = $this->json(
            'PUT',
            '/api/leads',
            $params,
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        $content = json_decode($response->getContent(), true);
        $leadId = $content['data']['id'];

        $this->assertDatabaseHas(Lead::getTableName(), [
            'identifier' => $leadId,
            'dealer_id' => $this->dealer->getKey()
        ]);

        $this->assertDatabaseHas(Customer::getTableName(), [
            'id' => $this->customer->getKey(),
            'dealer_id' => $this->dealer->getKey(),
            'website_lead_id' => $leadId
        ]);

        $this->assertDatabaseMissing(Customer::getTableName(), [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'dealer_id' => $this->dealer->getKey()
        ]);
    }

    /**
     * @group CRM
     * @covers ::index
     */
    public function testIndex()
    {
        $params = [
            'dealer_id' => $this->dealer->getKey(),
            'page' => 0,
            'per_page' => 10,
            'sort' => '-most_recent',
            'include' => 'otherLeadProperties'
        ];

        $response = $this->json(
            'GET',
            '/api/leads',
            $params,
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'identifier',
                        'website_id',
                        'dealer_id',
                        'name',
                        'lead_types',
                        'email',
                        'phone',
                        'preferred_contact',
                        'address',
                        'full_address',
                        'comments',
                        'note',
                        'referral',
                        'title',
                        'status',
                        'source',
                        'next_contact_date',
                        'contact_type',
                        'created_at',
                        'zip',
                        'is_archived',
                        'inventoryInterestedIn',
                        'otherLeadProperties'
                    ]
                ]
            ]);
    }

    /**
     * @group CRM
     * @covers ::index
     */
    public function testSearch()
    {
        $params = [
            'dealer_id' => $this->dealer->getKey(),
            'page' => 0,
            'per_page' => 10,
            'sort' => '-most_recent',
            'include' => 'otherLeadProperties',
            'search_term' => $this->lead->email_address
        ];

        $response = $this->json(
            'GET',
            '/api/leads',
            $params,
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'identifier',
                        'website_id',
                        'dealer_id',
                        'name',
                        'lead_types',
                        'email',
                        'phone',
                        'preferred_contact',
                        'address',
                        'full_address',
                        'comments',
                        'note',
                        'referral',
                        'title',
                        'status',
                        'source',
                        'next_contact_date',
                        'contact_type',
                        'created_at',
                        'zip',
                        'is_archived',
                        'inventoryInterestedIn',
                        'otherLeadProperties'
                    ]
                ]
            ])
            ->assertJsonFragment(['email' => $this->lead->email_address]);
    }

    /**
     * @group CRM
     * @covers ::getMatches
     */
    public function testGetMatches()
    {
        $params = [
            'leads' => [
                [
                    'type' => 'email',
                    'identifier' => $this->lead->email_address
                ]
            ]
        ];

        $response = $this->json(
            'POST',
            '/api/leads/find-matches?include=otherLeadProperties',
            $params,
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'identifier',
                        'website_id',
                        'dealer_id',
                        'name',
                        'lead_types',
                        'email',
                        'phone',
                        'preferred_contact',
                        'address',
                        'full_address',
                        'comments',
                        'note',
                        'referral',
                        'title',
                        'status',
                        'source',
                        'next_contact_date',
                        'contact_type',
                        'created_at',
                        'zip',
                        'is_archived',
                        'inventoryInterestedIn',
                        'otherLeadProperties'
                    ]
                ]
            ])
            ->assertJsonFragment(['email' => $this->lead->email_address]);
    }

    /**
     * @group CRM
     * @covers ::output
     */
    public function testOutput()
    {
        $params = [
            'archived' => 0,
            'dealer_id' => $this->dealer->getKey()
        ];

        $response = $this->json(
            'GET',
            '/api/leads/output',
            $params,
            ['access-token' => $this->token->access_token]
        );

        $output = $response->getContent();

        $this->assertStringContainsString('Email,Phone,"Preferred Contact","First Name","Last Name","Lead Type","Lead Source",Address,City,State,Zip,Status,"Closed Date",Comments,"Submission Date"',
            $output);
        $this->assertStringContainsString($this->lead->first_name, $output);
        $this->assertStringContainsString($this->lead->last_name, $output);
        $this->assertStringContainsString($this->lead->lead_type, $output);
        $this->assertStringContainsString($this->lead->address, $output);
        $this->assertStringContainsString($this->lead->city, $output);
        $this->assertStringContainsString($this->lead->state, $output);
        $this->assertStringContainsString($this->lead->zip, $output);
    }

    /**
     * @group CRM
     * @covers ::destroy
     */
    public function testDelete()
    {
        $response = $this->json(
            'DELETE',
            '/api/leads/'. $this->lead->getKey(),
            [],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        $this->assertDatabaseMissing(Lead::getTableName(), [
            'identifier' => $this->lead->getKey()
        ]);

    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdateLeadSource()
    {
        $source = $this->faker->company;
        $response = $this->json(
            'POST',
            '/api/leads/'. $this->lead->getKey(),
            [
                'lead_source' => $source
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas(LeadStatus::getTableName(), [
            'tc_lead_identifier' => $this->lead->getKey(),
            'source' => $source,
            'contact_type' => LeadStatus::TYPE_TASK
        ]);
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdateLeadTypes()
    {
        $types = $this->faker->randomElements(LeadType::TYPE_ARRAY, 3);
        $response = $this->json(
            'POST',
            '/api/leads/'. $this->lead->getKey(),
            [
                'lead_types' => $types
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        foreach ($types as $type)
            $this->assertDatabaseHas(LeadType::getTableName(), [
                'lead_id' => $this->lead->getKey(),
                'lead_type' => $type,
            ]);

        // confirm contact_type is not changed
        $this->assertDatabaseHas(LeadStatus::getTableName(), [
            'tc_lead_identifier' => $this->lead->getKey(),
            'contact_type' => LeadStatus::TYPE_TASK
        ]);
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdateLeadStatus()
    {
        $status = $this->faker->randomElement(LeadStatus::STATUS_ARRAY);
        $response = $this->json(
            'POST',
            '/api/leads/'. $this->lead->getKey(),
            [
                'lead_status' => $status
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas(LeadStatus::getTableName(), [
            'tc_lead_identifier' => $this->lead->getKey(),
            'status' => $status,
            'contact_type' => LeadStatus::TYPE_TASK
        ]);
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdateNote()
    {
        $note = $this->faker->sentence;
        $response = $this->json(
            'POST',
            '/api/leads/'. $this->lead->getKey(),
            [
                'note' => $note
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas(Lead::getTableName(), [
            'identifier' => $this->lead->getKey(),
            'note' => $note
        ]);
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdateCustomerDetails()
    {
        $this->assertDatabaseHas(Customer::getTableName(), [
            'website_lead_id' => $this->lead->getKey(),
            'first_name' => $this->lead->first_name,
            'last_name' => $this->lead->last_name,
            'email' => $this->lead->email_address,
            'work_phone' => $this->lead->phone_number
        ]);

        $newFirstName = $this->faker->firstName;
        $newLastName = $this->faker->lastName;
        $newMiddleName = $this->faker->suffix;
        $newEmail = $this->faker->email;
        $newPhone = $this->faker->e164PhoneNumber;
        $note = $this->faker->sentence;
        $comment = $this->faker->sentence;

        $response = $this->json(
            'POST',
            '/api/leads/'. $this->lead->getKey(),
            [
                'first_name' => $newFirstName,
                'middle_name' => $newMiddleName,
                'last_name' => $newLastName,
                'email_address' => $newEmail,
                'phone_number' => $newPhone,
                'note' => $note,
                'comments' => $comment
            ],
            ['access-token' => $this->token->access_token]
        );

        $this->assertDatabaseHas(Customer::getTableName(), [
            'website_lead_id' => $this->lead->getKey(),
            'first_name' => $newFirstName,
            'last_name' => $newLastName,
            'middle_name' => $newMiddleName,
            'email' => $newEmail,
            'work_phone' => $newPhone,
        ]);

        $this->assertDatabaseHas(Lead::getTableName(), [
            'identifier' => $this->lead->getKey(),
            'first_name' => $newFirstName,
            'last_name' => $newLastName,
            'middle_name' => $newMiddleName,
            'email_address' => $newEmail,
            'phone_number' => $newPhone,
            'note' => $note,
            'comments' => $comment
        ]);
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdateAppendInventory()
    {
        $this->assertEquals(4, InventoryLead::where(['website_lead_id' => $this->lead->getKey()])->count());

        foreach($this->inventories as $index => $inventory) {

            // confirm first 4 of 6 inventories are already assigned as units of interest
            if ($index < 4) {

                $this->assertDatabaseHas(InventoryLead::getTableName(), [
                    'website_lead_id' => $this->lead->getKey(),
                    'inventory_id' => $inventory->getKey()
                ]);

            } else {

                $this->assertDatabaseMissing(InventoryLead::getTableName(), [
                    'website_lead_id' => $this->lead->getKey(),
                    'inventory_id' => $inventory->getKey()
                ]);
            }
        }

        $response = $this->json(
            'POST',
            '/api/leads/'. $this->lead->getKey(),
            [
                'append_inventory' => $this->inventories->pluck('inventory_id')->toArray()
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        // confirm all 6 Unit of Interest are saved
        $this->assertEquals(6, InventoryLead::where(['website_lead_id' => $this->lead->getKey()])->count());

        foreach($this->inventories as $index => $inventory) {

            $this->assertDatabaseHas(InventoryLead::getTableName(), [
                'website_lead_id' => $this->lead->getKey(),
                'inventory_id' => $inventory->getKey()
            ]);
        }
    }

    /**
     * @group CRM
     * @covers ::mergeLeads
     */
    public function testMerge()
    {
        $leadIds = $this->leads->pluck('identifier')->toArray();

        $response = $this->json(
            'POST',
            '/api/leads/'. $this->lead->getKey() .'/merge',
            ['merge_lead_ids' => $leadIds],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas(Lead::getTableName(), [
            'identifier' => $this->lead->getKey()
        ]);

        $this->assertDatabaseHas(Interaction::getTableName(), [
            'tc_lead_id' => $this->lead->getKey(),
            'interaction_type' => Interaction::TYPE_INQUIRY
        ]);

        $this->assertEquals(0, Lead::whereIn('identifier', $leadIds)->count());

        $this->assertEquals(0, Interaction::whereIn('tc_lead_id', $leadIds)->count());
    }

    /**
     * @group CRM
     * @covers ::filters
     */
    public function testFilters()
    {
        $response = $this->json(
            'GET',
            '/api/leads/filters'
        );

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'sorts' => [
                    'created_at',
                    '-created_at',
                    'no_due_past_due_future_due',
                    'future_due_past_due_no_due',
                    '-most_recent',
                    'most_recent',
                    'status'
                ],
                'archived' => ['0', '-1', '1'],
                'filters' => [
                    '*' => [
                        'label',
                        'type',
                        'time',
                        'filters'
                    ]
                ]
            ]
        ]);
    }

    public function tearDown(): void
    {
        $userId = $this->dealer->newDealerUser->user_id;
        
        Interaction::where('user_id', $userId)->delete();
        InventoryLead::where('website_lead_id', $this->lead->getKey())->delete();
        Lead::where('dealer_id', $this->dealer->getKey())->delete();
        Inventory::where('dealer_id', $this->dealer->getKey())->delete();
        $this->website->delete();
        $this->location->delete();
        Customer::where('dealer_id', $this->dealer->getKey())->delete();

        $this->token->delete();

        // Delete Secondary User Data
        $this->salespersonToken->delete();
        DealerUserPermission::where('permission_level', $this->salesPerson->getKey())->delete();
        DealerUser::where('dealer_id', $this->dealer->getKey())->delete();
        $this->salesPerson->forceDelete();

        // Delete CRM User Related Data
        NewDealerUser::where(['user_id' => $userId])->delete();
        CrmUser::where(['user_id' => $userId])->delete();
        NewUser::destroy($userId);

        $this->dealer->delete();

        parent::tearDown();
    }
}