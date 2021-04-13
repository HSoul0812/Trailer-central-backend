<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadSource;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\Inventory\Inventory;
use App\Models\User\NewDealerUser;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\CRM\Leads\InquiryServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

/**
 * Test for App\Services\CRM\Leads\LeadService
 *
 * Class LeadServiceTest
 * @package Tests\Unit\Services\CRM\Leads
 *
 * @coversDefaultClass \App\Services\CRM\CRM\Leads\LeadService
 */
class InquiryServiceTest extends TestCase
{
    /**
     * @var LegacyMockInterface|LeadServiceInterface
     */
    private $leadServiceMock;

    /**
     * @var LegacyMockInterface|InquiryEmailServiceInterface
     */
    private $inquiryEmailServiceMock;

    /**
     * @var LegacyMockInterface|TrackingRepositoryInterface
     */
    private $trackingRepositoryMock;

    /**
     * @var LegacyMockInterface|TrackingUnitRepositoryInterface
     */
    private $trackingUnitRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->leadServiceMock = Mockery::mock(LeadServiceInterface::class);
        $this->app->instance(LeadServiceInterface::class, $this->leadServiceMock);

        $this->inquiryEmailServiceMock = Mockery::mock(InquiryEmailServiceInterface::class);
        $this->app->instance(InquiryEmailServiceInterface::class, $this->inquiryEmailServiceMock);

        $this->trackingRepositoryMock = Mockery::mock(TrackingRepositoryInterface::class);
        $this->app->instance(TrackingRepositoryInterface::class, $this->trackingRepositoryMock);

        $this->trackingUnitRepositoryMock = Mockery::mock(TrackingUnitRepositoryInterface::class);
        $this->app->instance(TrackingUnitRepositoryInterface::class, $this->trackingUnitRepositoryMock);
    }


    /**
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSend()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();
        $dealer = NewDealerUser::find($dealerId);
        $userId = $dealer->user_id;

        // Create Dummy Inventory
        $inventory = factory(Inventory::class)->create([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId
        ]);

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'website_id' => $websiteId,
            'inventory_id' => $inventory->inventory_id
        ]);
        $status = factory(LeadStatus::class)->create([
            'tc_lead_identifier' => $lead->identifier
        ]);

        // Create Source/Type/InventoryLead
        $source = factory(LeadSource::class)->create([
            'user_id' => $userId,
            'source_name' => $status->source
        ]);
        $type = factory(LeadType::class)->create([
            'lead_id' => $lead->identifier,
            'lead_type' => $lead->lead_type
        ]);
        $types = collect([$type]);
        $unit = factory(InventoryLead::class)->create([
            'website_lead_id' => $lead->identifier,
            'inventory_id' => $lead->inventory_id
        ]);
        $units = collect([$unit]);

        // Send Inquiry Params
        $sendInquiryParams = [
            'website_id' => $lead->website_id,
            'dealer_id' => $lead->dealer_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inventory_id' => $lead->inventory_id,
            'lead_type' => $lead->lead_type,
            'referral' => $lead->referral,
            'title' => $lead->title,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email_address' => $lead->email_address,
            'phone_number' => $lead->phone_number,
            'preferred_contact' => '',
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'zip' => $lead->zip,
            'comments' => $lead->comments,
            'date_submitted' => $lead->date_submitted->toDateTimeString(),
            'contact_email_sent' => $lead->date_submitted->toDateTimeString(),
            'adf_email_sent' => $lead->date_submitted->toDateTimeString(),
            'cdk_email_sent' => 1,
            'is_spam' => 0,
            'lead_source' => $status->source,
            'lead_status' => $status->status,
            'next_contact_date' => $status->next_contact_date,
            'contact_type' => $status->task,
            'sales_person_id' => $status->sales_person_id
        ];


        /** @var InquiryServiceInterface $service */
        $service = $this->app->make(InquiryServiceInterface::class);

        // Mock Fill Inquiry Lead
        $this->inquiryEmailServiceMock
            ->shouldReceive('fill')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($inquiry);

        // Mock Send Inquiry Lead
        $this->inquiryEmailServiceMock
            ->shouldReceive('send')
            ->once();

        // Mock Create Lead
        $this->leadServiceMock
            ->shouldReceive('create')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($lead);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($sendInquiryParams['cookie_session_id'], $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->once()
            ->with($sendInquiryParams['cookie_session_id'], $inquiry->itemId, $inquiry->getUnitType());

        // Expects Auto Assign Job
        $this->expectsJobs(AutoAssignJob::class);

        // Expects Auto Responder Job
        $this->expectsJobs(AutoResponderJob::class);


        // Validate Create Catalog Result
        $result = $service->create($createRequestParams);


        // Assert Match
        $this->assertSame($result->identifier, (int) $lead->identifier);

        // Assert Match
        $this->assertSame($result->leadStatus->id, $status->id);

        // Assert Match
        $this->assertSame($result->leadStatus->source, $source->source_name);


        // Match All Types
        $this->assertSame(count($result->leadTypes), $types->count());
        foreach($types as $k => $single) {
            $this->assertSame($result->leadTypes[$k], $single->lead_type);
        }

        // Match All Inventory Leads
        $this->assertSame($result->units->count(), $units->count());
        foreach($units as $k => $single) {
            $this->assertSame($result->units[$k]->inventory_id, $single->inventory_id);
        }
    }

    /**
     * @covers ::update
     *
     * @throws BindingResolutionException
     */
    public function testUpdateSingleType()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();
        $dealer = NewDealerUser::find($dealerId);
        $userId = $dealer->user_id;

        // Create Dummy Inventory
        $inventory = factory(Inventory::class)->create([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId
        ]);

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'website_id' => $websiteId,
            'inventory_id' => $inventory->inventory_id
        ]);
        $status = factory(LeadStatus::class)->create([
            'tc_lead_identifier' => $lead->identifier
        ]);

        // Create Source/Type/InventoryLead
        $source = factory(LeadSource::class)->create([
            'user_id' => $userId,
            'source_name' => $status->source
        ]);
        $type = factory(LeadType::class)->create([
            'lead_id' => $lead->identifier,
            'lead_type' => $lead->lead_type
        ]);
        $types = collect([$type]);
        $unit = factory(InventoryLead::class)->create([
            'website_lead_id' => $lead->identifier,
            'inventory_id' => $inventory->inventory_id
        ]);
        $units = collect([$unit]);

        // Create Base Lead Params
        $updateRequestParams = [
            'id' => $lead->identifier,
            'website_id' => $lead->website_id,
            'dealer_id' => $lead->dealer_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inventory_id' => $lead->inventory_id,
            'lead_type' => $lead->lead_type,
            'referral' => $lead->referral,
            'title' => $lead->title,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email_address' => $lead->email_address,
            'phone_number' => $lead->phone_number,
            'preferred_contact' => '',
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'zip' => $lead->zip,
            'comments' => $lead->comments,
            'date_submitted' => $lead->date_submitted->toDateTimeString(),
            'contact_email_sent' => $lead->date_submitted->toDateTimeString(),
            'adf_email_sent' => $lead->date_submitted->toDateTimeString(),
            'cdk_email_sent' => 1,
            'is_spam' => 0,
            'lead_source' => $status->source,
            'lead_status' => $status->status,
            'next_contact_date' => $status->next_contact_date,
            'contact_type' => $status->task,
            'sales_person_id' => $status->sales_person_id
        ];

        // Create Lead Params
        $updateLeadParams = $updateRequestParams;
        $updateLeadParams['inventory'] = [$updateRequestParams['inventory_id']];
        $updateLeadParams['lead_types'] = [$updateRequestParams['lead_type']];
        $updateLeadParams['preferred_contact'] = 'phone';

        // Create Lead Status Params
        $updateStatusParams = $updateLeadParams;
        $updateStatusParams['lead_id'] = $updateStatusParams['id'];

        // Create Source Params
        $createSourceParams = [
            'user_id' => $userId,
            'source_name' => $updateRequestParams['lead_source']
        ];


        /** @var LeadServiceInterface $service */
        $service = $this->app->make(LeadServiceInterface::class);

        // Mock Create Lead
        $this->leadRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($updateLeadParams)
            ->andReturn($lead);

        // Mock Sales Person Repository
        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with($updateStatusParams)
            ->andReturn($status);

        // Mock Source Repository
        $this->sourceRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with($createSourceParams)
            ->andReturn($source);

        // Mock Units of Interest
        $this->mockUnitsOfInterest($lead, $units);

        // Mock Lead Types
        $this->mockLeadTypes($lead, $types);
        

        // Validate Update Catalog Result
        $result = $service->update($updateRequestParams);


        // Assert Match
        $this->assertSame($result->identifier, (int) $lead->identifier);

        // Assert Match
        $this->assertSame($result->leadStatus->id, $status->id);

        // Assert Match
        $this->assertSame($result->leadStatus->source, $source->source_name);


        // Match All Types
        $this->assertSame(count($result->leadTypes), $types->count());
        foreach($types as $k => $single) {
            $this->assertSame($result->leadTypes[$k], $single->lead_type);
        }

        // Match All Inventory Leads
        $this->assertSame($result->units->count(), $units->count());
        foreach($units as $k => $single) {
            $this->assertSame($result->units[$k]->inventory_id, $single->inventory_id);
        }
    }


    /**
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreateMultiTypes()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();
        $dealer = NewDealerUser::find($dealerId);
        $userId = $dealer->user_id;

        // Create Dummy Inventory
        $units = factory(Inventory::class, 5)->create([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId
        ]);
        $inventory = $units->first();

        // Create Units of Interest Array
        $unitsInterest = [];
        foreach($units as $item) {
            $unitsInterest[] = $item->inventory_id;
        }

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'website_id' => $websiteId,
            'inventory_id' => $inventory->inventory_id
        ]);
        $status = factory(LeadStatus::class)->create([
            'tc_lead_identifier' => $lead->identifier
        ]);

        // Create Source/Type/InventoryLead
        $source = factory(LeadSource::class)->create([
            'user_id' => $userId,
            'source_name' => $status->source
        ]);


        // Create Dummy Lead Types
        $types = collect([]);
        $types->push(factory(LeadType::class)->create([
            'lead_id' => $lead->identifier,
            'lead_type' => LeadType::TYPE_GENERAL
        ]));
        $types->push(factory(LeadType::class)->create([
            'lead_id' => $lead->identifier,
            'lead_type' => LeadType::TYPE_BUILD
        ]));
        $types->push(factory(LeadType::class)->create([
            'lead_id' => $lead->identifier,
            'lead_type' => LeadType::TYPE_FINANCING
        ]));
        $leadType = $types->first();
        $lead->lead_type = $leadType->lead_type;

        // Get Lead Types
        $leadTypes = [];
        foreach($types as $type) {
            $leadTypes[] = $type->lead_type;
        }


        // Create Base Lead Params
        $createRequestParams = [
            'website_id' => $lead->website_id,
            'dealer_id' => $lead->dealer_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inventory' => $unitsInterest,
            'lead_types' => $leadTypes,
            'referral' => $lead->referral,
            'title' => $lead->title,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email_address' => $lead->email_address,
            'phone_number' => $lead->phone_number,
            'preferred_contact' => 'email',
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'zip' => $lead->zip,
            'comments' => $lead->comments,
            'date_submitted' => $lead->date_submitted->toDateTimeString(),
            'contact_email_sent' => $lead->date_submitted->toDateTimeString(),
            'adf_email_sent' => $lead->date_submitted->toDateTimeString(),
            'cdk_email_sent' => 1,
            'is_spam' => 0,
            'lead_source' => $status->source,
            'lead_status' => $status->status,
            'next_contact_date' => $status->next_contact_date,
            'contact_type' => $status->task,
            'sales_person_id' => $status->sales_person_id
        ];

        // Create Lead Params
        $createLeadParams = $createRequestParams;
        $createLeadParams['inventory_id'] = reset($unitsInterest);
        $createLeadParams['lead_type'] = reset($leadTypes);

        // Create Status Params
        $createStatusParams = $createLeadParams;
        $createStatusParams['lead_id'] = $lead->identifier;

        // Create Source Params
        $createSourceParams = [
            'user_id' => $userId,
            'source_name' => $createRequestParams['lead_source']
        ];


        /** @var LeadServiceInterface $service */
        $service = $this->app->make(LeadServiceInterface::class);

        // Mock Create Lead
        $this->leadRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createLeadParams)
            ->andReturn($lead);

        // Mock Sales Person Repository
        $this->statusRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createStatusParams)
            ->andReturn($status);

        // Mock Source Repository
        $this->sourceRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with($createSourceParams)
            ->andReturn($source);

        // Mock Units of Interest
        $this->mockUnitsOfInterest($lead, $units);

        // Mock Lead Types
        $this->mockLeadTypes($lead, $types);


        // Validate Create Catalog Result
        $result = $service->create($createRequestParams);


        // Assert Match
        $this->assertSame($result->identifier, (int) $lead->identifier);

        // Assert Match
        $this->assertSame($result->leadStatus->id, $status->id);

        // Assert Match
        $this->assertSame($result->leadStatus->source, $source->source_name);


        // Match All Types
        $this->assertSame(count($result->leadTypes), $types->count());
        foreach($result->leadTypes as $k => $single) {
            $this->assertTrue(in_array($single, $leadTypes));
        }

        // Match All Inventory Leads
        $this->assertSame($result->units->count(), $units->count());
        foreach($units as $k => $single) {
            $this->assertTrue(in_array($single->inventory_id, $unitsInterest));
        }
    }

    /**
     * @covers ::update
     *
     * @throws BindingResolutionException
     */
    public function testUpdateMultiTypes()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();
        $dealer = NewDealerUser::find($dealerId);
        $userId = $dealer->user_id;

        // Create Dummy Inventory
        $units = factory(Inventory::class, 5)->create([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId
        ]);
        $inventory = $units->first();

        // Create Units of Interest Array
        $unitsInterest = [];
        foreach($units as $item) {
            $unitsInterest[] = $item->inventory_id;
        }

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'website_id' => $websiteId,
            'inventory_id' => $inventory->inventory_id
        ]);
        $status = factory(LeadStatus::class)->create([
            'tc_lead_identifier' => $lead->identifier
        ]);

        // Create Source/Type/InventoryLead
        $source = factory(LeadSource::class)->create([
            'user_id' => $userId,
            'source_name' => $status->source
        ]);


        // Create Dummy Lead Types
        $types = collect([]);
        $types->push(factory(LeadType::class)->create([
            'lead_id' => $lead->identifier,
            'lead_type' => LeadType::TYPE_GENERAL
        ]));
        $types->push(factory(LeadType::class)->create([
            'lead_id' => $lead->identifier,
            'lead_type' => LeadType::TYPE_BUILD
        ]));
        $types->push(factory(LeadType::class)->create([
            'lead_id' => $lead->identifier,
            'lead_type' => LeadType::TYPE_FINANCING
        ]));
        $leadType = $types->first();
        $lead->lead_type = $leadType->lead_type;

        // Get Lead Types
        $leadTypes = [];
        foreach($types as $type) {
            $leadTypes[] = $type->lead_type;
        }

        // Update Base Lead Params
        $updateRequestParams = [
            'id' => $lead->identifier,
            'website_id' => $lead->website_id,
            'dealer_id' => $lead->dealer_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inventory' => $unitsInterest,
            'lead_types' => $leadTypes,
            'referral' => $lead->referral,
            'title' => $lead->title,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email_address' => $lead->email_address,
            'phone_number' => $lead->phone_number,
            'preferred_contact' => 'email',
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'zip' => $lead->zip,
            'comments' => $lead->comments,
            'date_submitted' => $lead->date_submitted->toDateTimeString(),
            'contact_email_sent' => $lead->date_submitted->toDateTimeString(),
            'adf_email_sent' => $lead->date_submitted->toDateTimeString(),
            'cdk_email_sent' => 1,
            'is_spam' => 0,
            'lead_source' => $status->source,
            'lead_status' => $status->status,
            'next_contact_date' => $status->next_contact_date,
            'contact_type' => $status->task,
            'sales_person_id' => $status->sales_person_id
        ];

        // Update Lead Params
        $updateLeadParams = $updateRequestParams;
        $updateLeadParams['inventory_id'] = reset($unitsInterest);
        $updateLeadParams['lead_type'] = reset($leadTypes);

        // Update Lead Status Params
        $updateStatusParams = $updateLeadParams;
        $updateStatusParams['lead_id'] = $updateStatusParams['id'];

        // Create Source Params
        $createSourceParams = [
            'user_id' => $userId,
            'source_name' => $updateRequestParams['lead_source']
        ];


        /** @var LeadServiceInterface $service */
        $service = $this->app->make(LeadServiceInterface::class);

        // Mock Create Lead
        $this->leadRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($updateLeadParams)
            ->andReturn($lead);

        // Mock Sales Person Repository
        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with($updateStatusParams)
            ->andReturn($status);

        // Mock Source Repository
        $this->sourceRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with($createSourceParams)
            ->andReturn($source);

        // Mock Units of Interest
        $this->mockUnitsOfInterest($lead, $units);

        // Mock Lead Types
        $this->mockLeadTypes($lead, $types);
        

        // Validate Update Catalog Result
        $result = $service->update($updateRequestParams);


        // Assert Match
        $this->assertSame($result->identifier, (int) $lead->identifier);

        // Assert Match
        $this->assertSame($result->leadStatus->id, $status->id);

        // Assert Match
        $this->assertSame($result->leadStatus->source, $source->source_name);


        // Match All Types
        $this->assertSame(count($result->leadTypes), $types->count());
        foreach($result->leadTypes as $k => $single) {
            $this->assertTrue(in_array($single, $leadTypes));
        }

        // Match All Inventory Leads
        $this->assertSame($result->units->count(), $units->count());
        foreach($units as $k => $single) {
            $this->assertTrue(in_array($single->inventory_id, $unitsInterest));
        }
    }


    /**
     * Mock All Units of Interest
     * 
     * This is the same regardless of what test its on.
     * 
     * @param Lead $lead
     * @param Collection<InventoryLead> $units
     * @return void
     */
    private function mockUnitsOfInterest(Lead $lead, Collection $units): void {
        // Mock Delete Unit Repository
        $this->unitRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(['website_lead_id' => $lead->identifier])
            ->andReturn(true);

        // Mock Create Unit Repository
        foreach($units as $unit) {
            $this->unitRepositoryMock
                ->shouldReceive('create')
                ->once()
                ->with([
                    'website_lead_id' => $lead->identifier,
                    'inventory_id' => $unit->inventory_id
                ])
                ->andReturn($unit);
        }
    }

    /**
     * Mock All Lead Types
     * 
     * This is the same regardless of what test its on.
     * 
     * @param Lead $lead
     * @param Collection<LeadType> $types
     * @return void
     */
    private function mockLeadTypes(Lead $lead, Collection $types): void {
        // Mock Delete Type Repository
        $this->typeRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(['lead_id' => $lead->identifier])
            ->andReturn(true);

        // Mock Create Type Repository
        foreach($types as $type) {
            $this->typeRepositoryMock
                ->shouldReceive('create')
                ->once()
                ->with([
                    'lead_id' => $lead->identifier,
                    'lead_type' => $type->lead_type
                ])
                ->andReturn($type);
        }
    }
}
