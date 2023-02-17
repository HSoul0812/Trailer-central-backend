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
        factory(Customer::class)->create([
            'first_name' => $this->lead->first_name,
            'last_name' => $this->lead->last_name,
            'email' => $this->lead->email_address,
            'home_phone' => $this->lead->phone_number,
            'work_phone' => $this->lead->phone_number,
            'cell_phone' => $this->lead->phone_number,
            'dealer_id' => $this->lead->dealer_id,
            'website_lead_id' => $this->lead->getKey()
        ]);
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

    public function tearDown(): void
    {
        $userId = $this->dealer->newDealerUser->user_id;
        
        Interaction::where('user_id', $userId)->delete();
        Lead::where('dealer_id', $this->dealer->getKey())->delete();

        $this->website->delete();

        $this->token->delete();

        // Delete CRM User Related Data
        NewDealerUser::where(['user_id' => $userId])->delete();
        CrmUser::where(['user_id' => $userId])->delete();
        NewUser::destroy($userId);

        $this->dealer->delete();

        parent::tearDown();
    }
}