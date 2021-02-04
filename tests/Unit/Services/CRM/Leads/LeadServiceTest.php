<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadSource;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\User\NewDealerUser;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use App\Repositories\CRM\Leads\TypeRepositoryInterface; 
use App\Repositories\CRM\Leads\UnitRepositoryInterface;
use App\Services\CRM\Leads\LeadServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
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
class LeadServiceTest extends TestCase
{
    /**
     * @var LegacyMockInterface|LeadRepositoryInterface
     */
    private $leadRepositoryMock;

    /**
     * @var LegacyMockInterface|StatusRepositoryInterface
     */
    private $statusRepositoryMock;

    /**
     * @var LegacyMockInterface|SourceRepositoryInterface
     */
    private $sourceRepositoryMock;

    /**
     * @var LegacyMockInterface|TypeRepositoryInterface
     */
    private $typeRepositoryMock;

    /**
     * @var LegacyMockInterface|UnitRepositoryInterface
     */
    private $unitRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->leadRepositoryMock = Mockery::mock(LeadRepositoryInterface::class);
        $this->app->instance(LeadRepositoryInterface::class, $this->leadRepositoryMock);

        $this->statusRepositoryMock = Mockery::mock(StatusRepositoryInterface::class);
        $this->app->instance(StatusRepositoryInterface::class, $this->statusRepositoryMock);

        $this->sourceRepositoryMock = Mockery::mock(SourceRepositoryInterface::class);
        $this->app->instance(SourceRepositoryInterface::class, $this->sourceRepositoryMock);

        $this->typeRepositoryMock = Mockery::mock(TypeRepositoryInterface::class);
        $this->app->instance(TypeRepositoryInterface::class, $this->typeRepositoryMock);

        $this->unitRepositoryMock = Mockery::mock(UnitRepositoryInterface::class);
        $this->app->instance(UnitRepositoryInterface::class, $this->unitRepositoryMock);
    }

    /**
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreateSingle()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $websiteId = self::getTestWebsiteRandom();
        $dealer = NewDealerUser::find($dealerId);
        $userId = $dealer->user_id;

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'website_id' => $websiteId
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

        // Create Base Lead Params
        $createRequestParams = [
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
            'date_submitted' => $lead->date_submitted,
            'contact_email_sent' => $lead->date_submitted,
            'adf_email_sent' => $lead->date_submitted,
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
        $createLeadParams['inventory'] = [$createRequestParams['inventory_id']];
        $createLeadParams['lead_types'] = [$createRequestParams['lead_type']];
        $createLeadParams['preferred_contact'] = 'phone';

        // Create Status Params
        $createStatusParams = $createLeadParams;
        $createStatusParams['lead_id'] = $lead->identifier;

        // Create Source Params
        $createSourceParams = [
            'user_id' => $userId,
            'source_name' => $createRequestParams['source_name']
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
        $this->assertSame($result->status->id, $status->id);

        // Assert Match
        $this->assertSame($result->status->source, $source->id);


        // Match All Types
        $this->assertSame(count($result->leadTypes), count($types));
        foreach($types as $k => $single) {
            $this->assertSame($result->leadTypes[$k]->id, $single->id);
        }

        // Match All Inventory Leads
        $this->assertSame(count($result->units), count($units));
        foreach($units as $k => $single) {
            $this->assertSame($result->inventory[$k]->id, $single->id);
        }
    }

    /**
     * @covers ::update
     *
     * @throws BindingResolutionException
     */
    public function testUpdateSingle()
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
            'lead_id' => $userId,
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
            'date_submitted' => $lead->date_submitted,
            'contact_email_sent' => $lead->date_submitted,
            'adf_email_sent' => $lead->date_submitted,
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

        // Create Source Params
        $createSourceParams = [
            'user_id' => $userId,
            'source_name' => $updateRequestParams['source_name']
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
            ->shouldReceive('update')
            ->once()
            ->with($updateLeadParams)
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
        $this->assertSame($result->status->id, $status->id);

        // Assert Match
        $this->assertSame($result->status->source, $source->id);


        // Match All Types
        $this->assertSame(count($result->leadTypes), count($types));
        foreach($types as $k => $single) {
            $this->assertSame($result->leadTypes[$k]->id, $single->id);
        }

        // Match All Inventory Leads
        $this->assertSame(count($result->units), count($units));
        foreach($units as $k => $single) {
            $this->assertSame($result->inventory[$k]->id, $single->id);
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
                ->with(Mockery::on(function($lead, $inventoryIds) use($unit) {
                    if(in_array($unit->inventory_id, $inventoryIds) &&
                       $lead->inventory_id === $unit->inventory_id) {
                        return true;
                    }
                    return false;
                }))
                ->andReturn($unit);
        }
    }

    /**
     * Mock All Lead Types
     * 
     * This is the same regardless of what test its on.
     * 
     * @param Lead $lead
     * @param Collection<LeadType> $units
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
                ->with(Mockery::on(function($lead, $types) use($type) {
                    if(in_array($type->lead_type, $types) &&
                       $lead->lead_type === $type->lead_type) {
                        return true;
                    }
                    return false;
                }))
                ->andReturn($type);
        }
    }
}
