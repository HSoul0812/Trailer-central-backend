<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadSource;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use App\Repositories\CRM\Leads\TypeRepositoryInterface; 
use App\Repositories\CRM\Leads\UnitRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\CRM\Leads\LeadServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
     * @const string
     */
    const TEST_SOURCE = 'Facebook';


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

    /**
     * @var LegacyMockInterface|InventoryRepositoryInterface
     */
    private $inventoryRepositoryMock;

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

        $this->inventoryRepositoryMock = Mockery::mock(InventoryRepositoryInterface::class);
        $this->app->instance(InventoryRepositoryInterface::class, $this->inventoryRepositoryMock);
    }


    /**
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreateSingleType()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;

        $status = $this->getEloquentMock(LeadStatus::class);
        $status->source_name = self::TEST_SOURCE;

        $source = $this->getEloquentMock(LeadSource::class);
        $source->source_name = self::TEST_SOURCE;

        $type = $this->getEloquentMock(LeadSource::class);
        $type->lead_type = LeadType::TYPE_INVENTORY;
        $types = collect([$type]);

        $unit = $this->getEloquentMock(Unit::class);
        $unit->inventory_id = 1;
        $units = collect([$unit]);

        // Create Base Lead Params
        $createRequestParams = [
            'inventory_id' => 1,
            'lead_type' => LeadType::TYPE_INVENTORY,
            'preferred_contact' => ''
        ];

        // Create Lead Params
        $createLeadParams = $createRequestParams;
        $createLeadParams['inventory'] = [$createRequestParams['inventory_id']];
        $createLeadParams['lead_types'] = [$createRequestParams['lead_type']];
        $createLeadParams['preferred_contact'] = 'phone';

        // Create Status Params
        $createStatusParams = $createLeadParams;
        $createStatusParams['lead_id'] = 1;

        // Create Source Params
        $createSourceParams = [
            'user_id' => 1,
            'source_name' => self::TEST_SOURCE
        ];


        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();


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
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;

        $status = $this->getEloquentMock(LeadStatus::class);
        $status->source_name = self::TEST_SOURCE;

        $source = $this->getEloquentMock(LeadSource::class);
        $source->source_name = self::TEST_SOURCE;

        $type = $this->getEloquentMock(LeadSource::class);
        $type->lead_type = LeadType::TYPE_INVENTORY;
        $types = collect([$type]);

        $unit = $this->getEloquentMock(Unit::class);
        $unit->inventory_id = 1;
        $units = collect([$unit]);

        $db = $this->getEloquentMock(DB::class);


        // Create Base Lead Params
        $updateRequestParams = [
            'id' => $lead->identifier,
            'inventory_id' => 1,
            'lead_type' => LeadType::TYPE_INVENTORY,
            'preferred_contact' => ''
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
            'user_id' => 1,
            'source_name' => $source->source_name
        ];



        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();

        $db->shouldReceive('transaction')->passthru();

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
    /*public function testCreateMultiTypes()
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
        /*$service = $this->app->make(LeadServiceInterface::class);

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
    }*/

    /**
     * @covers ::update
     *
     * @throws BindingResolutionException
     */
    /*public function testUpdateMultiTypes()
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
        /*$service = $this->app->make(LeadServiceInterface::class);

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
    }*/


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
        $inventoryIds = [];
        foreach($units as $unit) {
            $this->unitRepositoryMock
                ->shouldReceive('create')
                ->once()
                ->with([
                    'website_lead_id' => $lead->identifier,
                    'inventory_id' => $unit->inventory_id
                ])
                ->andReturn($unit);
            $inventoryIds[] = $unit->inventory_id;
        }

        // Mock Getting All Units
        $this->inventoryRepositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->with([
                'dealer_id' => $lead->dealer_id,
                InventoryRepositoryInterface::CONDITION_AND_WHERE_IN => [
                    'inventory_id' => $inventoryIds
                ]
            ])
            ->andReturn($units);
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
