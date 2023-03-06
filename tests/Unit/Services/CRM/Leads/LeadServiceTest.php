<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Exceptions\CRM\Leads\MergeLeadsException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadSource;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Interactions\Interaction;
use App\Models\User\NewDealerUser;
use App\Models\Inventory\Inventory;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\Interactions\Facebook\MessageRepositoryInterface;
use App\Repositories\CRM\Leads\FacebookRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use App\Repositories\CRM\Leads\TypeRepositoryInterface;
use App\Repositories\CRM\Leads\UnitRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\Dms\QuoteRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\CRM\Leads\LeadServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use Faker\Factory as Faker;

/**
 * Test for App\Services\CRM\Leads\LeadService
 *
 * Class LeadServiceTest
 * @package Tests\Unit\Services\CRM\Leads
 *
 * @coversDefaultClass \App\Services\CRM\Leads\LeadService
 */
class LeadServiceTest extends TestCase
{
    /**
     * @const string
     */
    const TEST_SOURCE = 'Facebook';

    /**
     * @const string
     */
    const TEST_FIRST_NAME = 'Alegra';
    const TEST_LAST_NAME = 'Johnson';
    const TEST_PHONE = '555-555-5555';
    const TEST_EMAIL = 'alegra@nowhere.com';
    const TEST_SALES_PERSON_ID = PHP_INT_MAX - 1;


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

    /**
     * @var LegacyMockInterface|InteractionsRepositoryInterface
     */
    private $interactionRepositoryMock;

    /**
     * @var LegacyMockInterface|MessageRepositoryInterface
     */
    private $fbMessageRepositoryMock;

    /**
     * @var LegacyMockInterface|EmailHistoryRepositoryInterface
     */
    private $emailHistoryRepositoryMock;

    /**
     * @var LegacyMockInterface|FacebookRepositoryInterface
     */
    private $facebookRepositoryMock;

    /**
     * @var LegacyMockInterface|TextRepositoryInterface
     */
    private $textRepositoryMock;

    /**
     * @var LegacyMockInterface|QuoteRepositoryInterface
     */
    private $quoteRepositoryMock;

    /**
     * @var LegacyMockInterface|CustomerRepositoryInterface
     */
    private $customerRepositoryMock;

    /**
     * @var LegacyMockInterface|TrackingRepositoryInterface
     */
    private $trackingRepositoryMock;

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

        $this->interactionRepositoryMock = Mockery::mock(InteractionsRepositoryInterface::class);
        $this->app->instance(InteractionsRepositoryInterface::class, $this->interactionRepositoryMock);

        $this->fbMessageRepositoryMock = Mockery::mock(MessageRepositoryInterface::class);
        $this->app->instance(MessageRepositoryInterface::class, $this->fbMessageRepositoryMock);

        $this->emailHistoryRepositoryMock = Mockery::mock(EmailHistoryRepositoryInterface::class);
        $this->app->instance(EmailHistoryRepositoryInterface::class, $this->emailHistoryRepositoryMock);

        $this->facebookRepositoryMock = Mockery::mock(FacebookRepositoryInterface::class);
        $this->app->instance(FacebookRepositoryInterface::class, $this->facebookRepositoryMock);

        $this->textRepositoryMock = Mockery::mock(TextRepositoryInterface::class);
        $this->app->instance(TextRepositoryInterface::class, $this->textRepositoryMock);

        $this->quoteRepositoryMock = Mockery::mock(QuoteRepositoryInterface::class);
        $this->app->instance(QuoteRepositoryInterface::class, $this->quoteRepositoryMock);

        $this->customerRepositoryMock = Mockery::mock(CustomerRepositoryInterface::class);
        $this->app->instance(CustomerRepositoryInterface::class, $this->customerRepositoryMock);

        $this->trackingRepositoryMock = Mockery::mock(TrackingRepositoryInterface::class);
        $this->app->instance(TrackingRepositoryInterface::class, $this->trackingRepositoryMock);

        $this->faker = Faker::create();
    }


    /**
     * @group CRM
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreateSingleType()
    {
        // Get Model Mocks
        $status = $this->getEloquentMock(LeadStatus::class);
        $status->id = 1;
        $status->source = self::TEST_SOURCE;

        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);
        $newDealerUser->id = 1;
        $newDealerUser->user_id = 1;

        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->leadStatus = $status;
        $lead->newDealerUser = $newDealerUser;

        $source = $this->getEloquentMock(LeadSource::class);
        $source->source_name = self::TEST_SOURCE;

        $type = $this->getEloquentMock(LeadType::class);
        $type->lead_type = LeadType::TYPE_INVENTORY;
        $leadTypes = [$type->lead_type];
        $types = collect([$type]);

        $unit = $this->getEloquentMock(Unit::class);
        $unit->inventory_id = 1;
        $units = collect([$unit]);

        // Create Base Lead Params
        $createRequestParams = [
            'inventory_id' => 1,
            'lead_type' => LeadType::TYPE_INVENTORY,
            'preferred_contact' => '',
            'lead_source' => self::TEST_SOURCE,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName()
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
            'user_id' => $newDealerUser->user_id,
            'source_name' => self::TEST_SOURCE
        ];


        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();


        // @var LeadServiceInterface $service
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

        // Mock Customer Repository
        $this->customerRepositoryMock
            ->shouldReceive('createFromLead')
            ->once()
            ->with($lead);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->twice()
             ->andReturn($leadTypes);

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
     * @group CRM
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreateMultiTypes()
    {
        // Get Model Mocks
        $status = $this->getEloquentMock(LeadStatus::class);
        $status->id = 1;
        $status->source = self::TEST_SOURCE;

        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);
        $newDealerUser->id = 1;
        $newDealerUser->user_id = 1;

        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->leadStatus = $status;
        $lead->newDealerUser = $newDealerUser;

        $source = $this->getEloquentMock(LeadSource::class);
        $source->source_name = self::TEST_SOURCE;

        $types = collect([]);
        $leadTypes = [LeadType::TYPE_INVENTORY, LeadType::TYPE_GENERAL, LeadType::TYPE_BUILD];
        foreach($leadTypes as $i => $v) {
            $type = $this->getEloquentMock(LeadType::class);
            $type->lead_type = $v;
            $types->push($type);
        }

        $units = collect([]);
        $unitsInterest = [];
        for($i = 1; $i <= 5; $i++) {
            $unit = $this->getEloquentMock(Unit::class);
            $unit->inventory_id = $i;
            $units->push($unit);
            $unitsInterest[] = $i;
        }
        $inventory = $units->first();


        // Create Base Lead Params
        $createRequestParams = [
            'inventory' => $unitsInterest,
            'lead_types' => $leadTypes,
            'preferred_contact' => '',
            'lead_source' => self::TEST_SOURCE,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName()
        ];

        // Create Lead Params
        $createLeadParams = $createRequestParams;
        $createLeadParams['preferred_contact'] = 'phone';
        $createLeadParams['inventory_id'] = reset($unitsInterest);
        $createLeadParams['lead_type'] = reset($leadTypes);

        // Create Status Params
        $createStatusParams = $createLeadParams;
        $createStatusParams['lead_id'] = $lead->identifier;

        // Create Source Params
        $createSourceParams = [
            'user_id' => $newDealerUser->user_id,
            'source_name' => $createRequestParams['lead_source']
        ];


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();

        // @var LeadServiceInterface $service
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

        // Mock Customer Repository
        $this->customerRepositoryMock
            ->shouldReceive('createFromLead')
            ->once()
            ->with($lead);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->twice()
             ->andReturn($leadTypes);

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
     * @group CRM
     * @covers ::create
     */
    public function testCreateWithSalespersonId()
    {
        // Get Model Mocks
        $status = $this->getEloquentMock(LeadStatus::class);
        $status->id = 1;
        $status->sales_person_id = self::TEST_SALES_PERSON_ID;

        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->leadStatus = $status;

        // Create Request Params
        $createRequestParams = [
            'sales_person_id' => self::TEST_SALES_PERSON_ID,
            'preferred_contact' => '',
        ];
        
        // Create Lead Params
        $createLeadParams = $createRequestParams;
        $createLeadParams['preferred_contact'] = 'phone';

        // Create Status Params
        $createStatusParams = $createLeadParams;
        $createStatusParams['lead_id'] = $lead->identifier;

        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();

        // @var LeadServiceInterface $service
        $service = $this->app->make(LeadServiceInterface::class);

        // Mock Create Lead
        $this->leadRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createLeadParams)
            ->andReturn($lead);

        // Mock Status Repository
        $this->statusRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createStatusParams)
            ->andReturn($status);

        // Create Lead
        $result = $service->create($createRequestParams);

        // Assert Match
        $this->assertSame($result->identifier, $lead->identifier);
        $this->assertSame($result->leadStatus->id, $status->id);
        $this->assertSame($result->leadStatus->sales_person_id, $status->sales_person_id);
    }



    /**
     * @group CRM
     * @covers ::update
     *
     * @throws BindingResolutionException
     */
    public function testUpdateSingleType()
    {
        $status = $this->getEloquentMock(LeadStatus::class);
        $status->id = 1;
        $status->source = self::TEST_SOURCE;

        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);
        $newDealerUser->id = 1;
        $newDealerUser->user_id = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->leadStatus = $status;
        $lead->newDealerUser = $newDealerUser;

        $source = $this->getEloquentMock(LeadSource::class);
        $source->source_name = self::TEST_SOURCE;

        $type = $this->getEloquentMock(LeadType::class);
        $type->lead_type = LeadType::TYPE_INVENTORY;
        $types = collect([$type]);

        $unit = $this->getEloquentMock(Unit::class);
        $unit->inventory_id = 1;
        $units = collect([$unit]);

        // Create Base Lead Params
        $updateRequestParams = [
            'id' => $lead->identifier,
            'inventory_id' => 1,
            'lead_type' => LeadType::TYPE_INVENTORY,
            'preferred_contact' => '',
            'lead_source' => self::TEST_SOURCE
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

        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
            ->twice()
            ->andReturn([LeadType::TYPE_INVENTORY]);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollback')->never();
        Log::shouldReceive('error')->never();

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

        // Mock Update Customer
        $this->mockUpdateCustomer($lead, $updateRequestParams);

        // @var LeadServiceInterface $service
        $service = $this->app->make(LeadServiceInterface::class);

        // Validate Update Catalog Result
        $result = $service->update($updateRequestParams);

        $this->assertInstanceOf(Lead::class, $result);

        // Assert Match
        $this->assertSame($result->identifier, $lead->identifier);

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
     * @group CRM
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
        $newDealerUser = NewDealerUser::find($dealerId);
        $userId = $newDealerUser->user_id;

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
            'user_id' => $newDealerUser->user_id,
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
            'user_id' => $newDealerUser->user_id,
            'source_name' => $updateRequestParams['lead_source']
        ];

        // @var LeadServiceInterface $service
        $service = $this->app->make(LeadServiceInterface::class);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollback')->never();
        Log::shouldReceive('error')->never();

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

        // Mock updateCustomer()
        $this->mockUpdateCustomer($lead, $updateRequestParams);

        // Validate Update Catalog Result
        $result = $service->update($updateRequestParams);

        // Assert Match
        $this->assertSame($result->identifier, $lead->identifier);

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
     * @group CRM
     * @covers ::update
     *
     * @throws BindingResolutionException
     */
    public function testUpdateAppendInventory()
    {
        $status = $this->getEloquentMock(LeadStatus::class);
        $status->id = 1;
        $status->source = self::TEST_SOURCE;

        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);
        $newDealerUser->id = 1;
        $newDealerUser->user_id = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->leadStatus = $status;
        $lead->newDealerUser = $newDealerUser;

        $units = collect([]);
        $unitsInterest = [];
        for($i = 3; $i <= 6; $i++) {
            $unit = $this->getEloquentMock(Unit::class);
            $unit->inventory_id = $i;
            $units->push($unit);
            $unitsInterest[] = $i;
        }

        $existingUnits = collect([]);
        $existingUnitIds = [];
        for($i = 1; $i <= 4; $i++) {
            $unit = $this->getEloquentMock(Unit::class);
            $unit->inventory_id = $i;
            $existingUnits->push($unit);
            $existingUnitIds[] = $i;
        }

        $missingUnits = collect([]);
        for($i = 5; $i <= 6; $i++) {
            $unit = $this->getEloquentMock(Unit::class);
            $unit->inventory_id = $i;
            $missingUnits->push($unit);
        }

        $allUnits = collect([]);
        for($i = 1; $i <= 6; $i++) {
            $unit = $this->getEloquentMock(Unit::class);
            $unit->inventory_id = $i;
            $allUnits->push($unit);
        }

        $updateRequestParams = [
            'id' => $lead->identifier,
            'preferred_contact' => 'phone',
            'append_inventory' => $unitsInterest
        ];

        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollback')->never();
        Log::shouldReceive('error')->never();

        // Mock Update Lead
        $this->leadRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($updateRequestParams)
            ->andReturn($lead);

        // Mock Sales Person Repository
        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once($status);

        // Mock Source Repository
        $this->sourceRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->never();

        // Mock appendUnitsOfInterest()
        $this->unitRepositoryMock->shouldReceive('delete')->never();

        $this->unitRepositoryMock->shouldReceive('getUnitIds')
            ->once()
            ->with($lead->identifier)
            ->andReturn($existingUnitIds);
        
        foreach ($missingUnits as $unit) {

            $this->unitRepositoryMock->shouldReceive('create')
                ->once()->with([
                    'inventory_id' => $unit->inventory_id,
                    'website_lead_id' => $lead->identifier
                ])->andReturn($unit);
        }

        $this->inventoryRepositoryMock->shouldReceive('getAll')->once()
            ->andReturn($allUnits);
        // end Mock appendUnitsOfInterest()

        // Mock updateLeadTypes()
        $this->typeRepositoryMock->shouldNotReceive('delete');
        $this->typeRepositoryMock->shouldNotReceive('create');

        // Mock updateCustomer()
        $this->mockUpdateCustomer($lead, $updateRequestParams);

        // @var LeadServiceInterface $service
        $service = $this->app->make(LeadServiceInterface::class);

        // Validate Update
        $result = $service->update($updateRequestParams);

        $this->assertInstanceOf(Lead::class, $result);

        // Assert Match
        $this->assertSame($result->identifier, $lead->identifier);

        // Match All Inventory Leads
        $this->assertSame($result->units->count(), $allUnits->count());
        foreach($allUnits as $index => $unit) {
            $this->assertSame($result->units[$index]->inventory_id, $unit->inventory_id);
        }
    }

    /**
     * @group CRM
     * @covers ::mergeInquiry
     *
     * @throws BindingResolutionException
     */
    public function testMergeInquiry()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $salesPerson = $this->getEloquentMock(SalesPerson::class);
        $salesPerson->first_name = self::TEST_FIRST_NAME;
        $salesPerson->last_name = self::TEST_LAST_NAME;

        $status = $this->getEloquentMock(LeadStatus::class);
        $status->salesPerson = $salesPerson;
        $lead->leadStatus = $status;
        $lead->units = new Collection();

        $interaction = $this->getEloquentMock(Interaction::class);
        $interaction->interaction_id = 1;
        $interaction->leadStatus = $status;
        $interaction->emailHistory = new Collection();

        // Send Request Params
        $mergeLeadParams = [
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email_address' => $lead->email_address,
            'phone_number' => '1234567890',
            'comments' => 'This is a New Merge\'s Comments!'
        ];

        // Create Notes
        $notes = $mergeLeadParams['first_name'] . ' ' . $mergeLeadParams['last_name'] . '<br /><br />' .
                 'Phone: ' . $mergeLeadParams['phone_number'] . '<br /><br />' .
                 'Email: ' . $mergeLeadParams['email_address'] . '<br /><br />' .
                 $mergeLeadParams['comments'];

        // Create Interaction Params
        $createInteractionParams = [
            'lead_id' => $lead->identifier,
            'interaction_type' => 'INQUIRY',
            'interaction_notes' => 'Original Inquiry: ' . $notes
        ];


        // @var LeadServiceInterface $service
        $service = $this->app->make(LeadServiceInterface::class);

        // Mock Create Interaction
        $this->interactionRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createInteractionParams)
            ->andReturn($interaction);


        // Validate Send Inquiry Result
        $result = $service->mergeInquiry($lead, $mergeLeadParams);

        // Match Merged Lead Details
        $this->assertSame($result->interaction_id, $interaction->interaction_id);
    }

    /**
     * @group CRM
     * @covers ::mergeLeadData
     */
    public function testMergeLeadData()
    {
        $leadId = PHP_INT_MAX;
        $mergesLeadId = PHP_INT_MAX - 1;

        $params = [
            'lead_id' => $leadId,
            'search' => ['lead_id' => $mergesLeadId]
        ];

        $customerParams = [
            'website_lead_id' => $leadId,
            'search' => ['website_lead_id' => $mergesLeadId]
        ];

        /** @var LegacyMockInterface|LeadServiceInterface $service */
        $service = $this->app->make(LeadServiceInterface::class);

        DB::shouldReceive('beginTransaction')
            ->once();

        $this->emailHistoryRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->with($params)
            ->once();

        $this->facebookRepositoryMock
            ->shouldReceive('bulkUpdateFbLead')
            ->with($params)
            ->once();

        $this->textRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->with($params)
            ->once();

        $this->quoteRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->with($params)
            ->once();

        $this->customerRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->with($customerParams)
            ->once();

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->with(['id' => $leadId])
            ->once()
            ->andReturn($this->getEloquentMock(Lead::class));

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->with(['id' => $mergesLeadId])
            ->once()
            ->andReturn($this->getEloquentMock(Lead::class));

        $this->interactionRepositoryMock
            ->shouldReceive('batchUpdate')
            ->with(['tc_lead_id' => $leadId], ['tc_lead_id' => $mergesLeadId])
            ->once();

        $this->interactionRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->getEloquentMock(Interaction::class));

        $this->trackingRepositoryMock
            ->shouldReceive('batchUpdate')
            ->with(['lead_id' => $leadId], ['lead_id' => $mergesLeadId])
            ->once();

        $this->unitRepositoryMock
            ->shouldReceive('getUnitIds')
            ->with($leadId)
            ->once();

        $this->unitRepositoryMock
            ->shouldReceive('getUnitIds')
            ->with($mergesLeadId)
            ->once();

        DB::shouldReceive('commit')
            ->once();

        Log::shouldReceive('info')
            ->with('leads has been successfully merged', ['leadId' => $leadId, 'oldLeadId' => $mergesLeadId])
            ->once();

        Log::shouldReceive('error')
            ->never();

        DB::shouldReceive('rollback')
            ->never();

        $result = $service->mergeLeadData($leadId, $mergesLeadId);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::mergeLeadData
     */
    public function testMergeLeadDataWithError()
    {
        $leadId = PHP_INT_MAX;
        $mergesLeadId = PHP_INT_MAX - 1;

        $exception = new \Exception();

        $params = [
            'lead_id' => $leadId,
            'search' => ['lead_id' => $mergesLeadId]
        ];

        /** @var LegacyMockInterface|LeadServiceInterface $service */
        $service = $this->app->make(LeadServiceInterface::class);

        DB::shouldReceive('beginTransaction')
            ->once();

        $this->emailHistoryRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->with($params)
            ->once();

        $this->facebookRepositoryMock
            ->shouldReceive('bulkUpdateFbLead')
            ->with($params)
            ->once()
            ->andThrow($exception);;

        $this->textRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->with($params)
            ->never();

        $this->quoteRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->with($params)
            ->never();

        $this->customerRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->never();

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->with(['id' => $leadId])
            ->andReturn($this->getEloquentMock(Lead::class));

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->with(['id' => $mergesLeadId])
            ->never();

        DB::shouldReceive('rollback')
            ->once();

        Log::shouldReceive('error')
            ->once();

        $this->expectException(MergeLeadsException::class);

        $service->mergeLeadData($leadId, $mergesLeadId);
    }

    /**
     * @group CRM
     * @covers ::mergeLeads
     */
    public function testMergeLeads()
    {
        $leadId = PHP_INT_MAX;
        $mergeLeadIds = [PHP_INT_MAX - 1, PHP_INT_MAX - 2];

        $this->leadRepositoryMock
            ->shouldReceive('getNotesBetweenLeads')
            ->once();

        $this->leadRepositoryMock
            ->shouldReceive('getMinSubmittedDateBetweenLeads')
            ->once();

        $this->leadRepositoryMock
            ->shouldReceive('update')
            ->once();

        $this->leadRepositoryMock
            ->shouldReceive('getMaxContactDateBetweenLeads')
            ->once();

        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once();

        $this->leadRepositoryMock
            ->shouldReceive('delete')
            ->times(count($mergeLeadIds));


        // Mocking @mergeLeadData

        $this->emailHistoryRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->times(count($mergeLeadIds));

        $this->facebookRepositoryMock
            ->shouldReceive('bulkUpdateFbLead')
            ->times(count($mergeLeadIds));

        $this->textRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->times(count($mergeLeadIds));

        $this->quoteRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->times(count($mergeLeadIds));

        $this->customerRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->times(count($mergeLeadIds));

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->with(['id' => $leadId])
            ->times(count($mergeLeadIds))
            ->andReturn($this->getEloquentMock(Lead::class));

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->times(count($mergeLeadIds))
            ->andReturn($this->getEloquentMock(Lead::class));

        $this->interactionRepositoryMock
            ->shouldReceive('batchUpdate')
            ->times(count($mergeLeadIds));

        $this->interactionRepositoryMock
            ->shouldReceive('create')
            ->times(count($mergeLeadIds))
            ->andReturn($this->getEloquentMock(Interaction::class));

        $this->trackingRepositoryMock
            ->shouldReceive('batchUpdate')
            ->times(count($mergeLeadIds));

        $this->unitRepositoryMock
            ->shouldReceive('getUnitIds')
            ->with($leadId)
            ->times(count($mergeLeadIds));

        $this->unitRepositoryMock
            ->shouldReceive('getUnitIds')
            ->times(count($mergeLeadIds));
    

        /** @var LegacyMockInterface|LeadServiceInterface $service */
        $service = $this->app->make(LeadServiceInterface::class);

        $service->mergeLeads($leadId, $mergeLeadIds);
    }

    /**
     * @group CRM
     * @covers ::mergeUnits
     */
    public function testMergeUnits()
    {
        $leadId = PHP_INT_MAX;
        $mergeLeadId = PHP_INT_MAX - 1;

        $this->unitRepositoryMock
            ->shouldReceive('getUnitIds')
            ->with($leadId)
            ->once()
            ->andReturn([1, 2]);

        $this->unitRepositoryMock
            ->shouldReceive('getUnitIds')
            ->with($mergeLeadId)
            ->once()
            ->andReturn([2, 3]);

        $this->unitRepositoryMock
            ->shouldReceive('create')
            ->with([
                'inventory_id' => 3,
                'website_lead_id' => $leadId
            ])
            ->once();

        /** @var LegacyMockInterface|LeadServiceInterface $service */
        $service = $this->app->make(LeadServiceInterface::class);

        $service->mergeUnits($leadId, $mergeLeadId);
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

    /**
     * Mock Update Customer
     *
     * @param Lead $lead
     * @param array $params
     * @return void
     */
    private function mockUpdateCustomer(Lead $lead, array $params)
    {
        $customerParams = [];
        foreach ($params as $key => $value) {

            if (in_array($key, array_keys(Lead::CUSTOMER_FIELDS))) {
                $customerParams[Lead::CUSTOMER_FIELDS[$key]] = $value;
            }
        }

        if (count($customerParams) > 0) {
            $this->customerRepositoryMock->shouldReceive('bulkUpdate')
                ->with(array_merge($customerParams, [
                    'search' => [
                        'website_lead_id' => $lead->identifier
                    ],

                ]))
                ->once();
        } else {
            $this->customerRepositoryMock->shouldReceive('bulkUpdate')
                ->never();
        }
    }
}
