<?php

namespace Tests\Integration\Http\Controllers\CRM\Leads;

use Tests\Integration\IntegrationTestCase;
use Tests\database\seeds\CRM\Leads\InquirySeeder;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\CRM\Leads\LeadType;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Leads\InventoryLead;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Models\CRM\Leads\LeadStatus;
use Illuminate\Support\Facades\Log;
use Faker\Factory as Faker;
use App\Models\CRM\User\Customer;

class InquiryControllerTest extends IntegrationTestCase
{
    public function testCreateChecksForBannedLeadTexts()
    {
        $params = [
            'comments' => 'your website'
        ];

        $response = $this->json(
            'PUT',
            '/api/inquiry/create',
            $params,
            ['access-token' => $this->accessToken()]
        );

        $response
            ->assertStatus(422)
            ->assertSee('Lead contains banned text.');
    }

    public function testSendChecksForBannedLeadTexts()
    {
        $params = [
            'comments' => 'a website that your organization hosts'
        ];

        $response = $this->json(
            'PUT',
            '/api/inquiry/send',
            $params,
            ['access-token' => $this->accessToken()]
        );

        $response
            ->assertStatus(422)
            ->assertSee('Lead contains banned text.');
    }

    public function testTextChecksForBannedLeadTexts()
    {
        $params = [
            'sms_message' => 'a website that your organization hosts'
        ];

        $response = $this->json(
            'PUT',
            '/api/inquiry/text',
            $params,
            ['access-token' => $this->accessToken()]
        );

        $response
            ->assertStatus(422)
            ->assertSee('Lead contains banned text.');
    }

    public function testCreateAutoMergeCaseInsensitiveName()
    {
        $seeder = new InquirySeeder;

        $seeder->seed();

        // send an inquiry with the same customer details but different letter case
        $params = [
            'website_id' => $seeder->website->getKey(),
            'inquiry_type' => InquiryLead::INQUIRY_TYPE_DEFAULT,
            'lead_types' => [LeadType::TYPE_MANUAL],
            'first_name' => strtoupper($seeder->lead->first_name),
            'last_name' => strtoupper($seeder->lead->last_name),
            'email_address' => $seeder->lead->email_address,
            'inventory' => [$seeder->anotherInventory->getKey()]
        ];

        $response = $this->json(
            'PUT',
            '/api/inquiry/create',
            $params,
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200);

        // confirm that no new lead is created
        $this->assertEquals(1, Lead::where('dealer_id', $seeder->dealer->getKey())->count());

        // confirm that new inquiry interaction is created
        $this->assertEquals(1, $seeder->lead->interactions()
            ->where('interaction_type', 'INQUIRY')
            ->where('interaction_notes', 'like', 'Original Inquiry: '. strtoupper($seeder->lead->first_name) .' '. strtoupper($seeder->lead->last_name) .'%')->count());

        // confirm lead types
        $this->assertDatabaseHas(LeadType::getTableName(), [
            'lead_id' => $seeder->lead->getKey(),
            'lead_type' => LeadType::TYPE_MANUAL
        ]);

        $this->assertDatabaseHas(LeadType::getTableName(), [
            'lead_id' => $seeder->lead->getKey(),
            'lead_type' => LeadType::TYPE_GENERAL
        ]);

        // confirm units of interest
        $this->assertDatabaseHas('crm_inventory_lead', [
            'website_lead_id' => $seeder->lead->getKey(),
            'inventory_id' => $seeder->lead->inventory_id
        ]);

        $this->assertDatabaseHas('crm_inventory_lead', [
            'website_lead_id' => $seeder->lead->getKey(),
            'inventory_id' => $seeder->anotherInventory->inventory_id
        ]);

        $seeder->cleanUp();
    }

    public function testAutoMergeArchivedLead()
    {
        $seeder = new InquirySeeder;

        $seeder->seed();

        // close Lead
        $statusRepo = app(StatusRepositoryInterface::class);
        $statusRepo->createOrUpdate(['lead_id' => $seeder->lead->getKey(), 'lead_status' => LeadStatus::STATUS_LOST]);

        // archive Lead
        $leadRepo = app(LeadRepositoryInterface::class);
        $leadRepo->update(['id' => $seeder->lead->getKey(), 'is_archived' => Lead::LEAD_ARCHIVED]);

        // send a new inquiry
        $params = [
            'website_id' => $seeder->website->getKey(),
            'inquiry_type' => InquiryLead::INQUIRY_TYPE_DEFAULT,
            'lead_types' => [LeadType::TYPE_MANUAL],
            'first_name' => $seeder->lead->first_name,
            'last_name' => $seeder->lead->last_name,
            'email_address' => $seeder->lead->email_address,
            'inventory' => [$seeder->anotherInventory->getKey()]
        ];

        $response = $this->json(
            'PUT',
            '/api/inquiry/create',
            $params,
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200);

        // confirm that no new lead is created
        $this->assertEquals(1, Lead::where('dealer_id', $seeder->dealer->getKey())->count());

        // confirm that a new inquiry interaction is created
        $this->assertEquals(1, $seeder->lead->interactions()
            ->where('interaction_type', 'INQUIRY')
            ->where('interaction_notes', 'like', 'Original Inquiry: '. $seeder->lead->first_name .' '. $seeder->lead->last_name .'%')->count());

        // confirm lead types
        $this->assertDatabaseHas(LeadType::getTableName(), [
            'lead_id' => $seeder->lead->getKey(),
            'lead_type' => LeadType::TYPE_MANUAL
        ]);

        $this->assertDatabaseHas(LeadType::getTableName(), [
            'lead_id' => $seeder->lead->getKey(),
            'lead_type' => LeadType::TYPE_GENERAL
        ]);

        // confirm units of interest
        $this->assertDatabaseHas('crm_inventory_lead', [
            'website_lead_id' => $seeder->lead->getKey(),
            'inventory_id' => $seeder->lead->inventory_id
        ]);

        $this->assertDatabaseHas('crm_inventory_lead', [
            'website_lead_id' => $seeder->lead->getKey(),
            'inventory_id' => $seeder->anotherInventory->inventory_id
        ]);

        // confirm Lead status is changed to New Inquiry
        $this->assertDatabaseHas(LeadStatus::getTableName(), [
            'tc_lead_identifier' => $seeder->lead->getKey(),
            'status' => LeadStatus::STATUS_NEW_INQUIRY
        ]);

        // confirm Lead is unarchived
        $this->assertDatabaseHas(Lead::getTableName(), [
            'identifier' => $seeder->lead->getKey(),
            'is_archived' => Lead::NOT_ARCHIVED
        ]);

        $seeder->cleanUp();
    }

    public function testCreate()
    {
        $seeder = new InquirySeeder;
        $faker = Faker::create();

        $firstName = $faker->firstName();
        $lastName = $faker->lastName();
        $email = $faker->email();

        $seeder->seed();

        // send a new inquiry
        $params = [
            'website_id' => $seeder->website->getKey(),
            'inquiry_type' => InquiryLead::INQUIRY_TYPE_DEFAULT,
            'lead_types' => [LeadType::TYPE_MANUAL],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email_address' => $email,
        ];

        $response = $this->json(
            'PUT',
            '/api/inquiry/create',
            $params,
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200);

        // confirm Lead is created
        $this->assertDatabaseHas(Lead::getTableName(), [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email_address' => $email,
        ]);

        // confirm Customer is created
        $this->assertDatabaseHas(Customer::getTableName(), [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ]);

        $seeder->cleanUp();
    }

    public function testCreateWithExistingCustomer()
    {
        $seeder = new InquirySeeder;

        $seeder->seed();

        $firstName = $seeder->customer->first_name;
        $lastName = $seeder->customer->last_name;
        $email = $seeder->customer->email;

        // send a new inquiry
        $params = [
            'website_id' => $seeder->website->getKey(),
            'inquiry_type' => InquiryLead::INQUIRY_TYPE_DEFAULT,
            'lead_types' => [LeadType::TYPE_MANUAL],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email_address' => $email,
        ];

        $response = $this->json(
            'PUT',
            '/api/inquiry/create',
            $params,
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200);

        // confirm Lead is created
        $this->assertDatabaseHas(Lead::getTableName(), [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email_address' => $email,
        ]);

        // confirm new Customer is not created
        $this->assertEquals(1, Customer::where([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ])->count());

        $seeder->cleanUp();
    }
}
