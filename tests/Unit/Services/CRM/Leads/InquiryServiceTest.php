<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Jobs\CRM\Leads\AutoAssignJob;
use App\Jobs\Email\AutoResponderJob;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Services\CRM\Leads\Export\ADFServiceInterface;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\CRM\Leads\InquiryServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
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
     * @const string
     */
    const TEST_DEVICE = 'PHPUnit';

    /**
     * @const array
     */
    const TEST_WEBSITE_CONFIG = [
        'logo' => 'https://dashboard.trailercentral.com/images/logo2.png',
        'logoUrl' => 'https://trailercentral.com',
        'fromName' => 'Trailer Central'
    ];

    /**
     * @const string
     */
    const TEST_INQUIRY_EMAIL = 'admin@operatebeyond.com';
    const TEST_INQUIRY_NAME = 'Operate Beyond';

    /**
     * @const string
     */
    const TEST_DOMAIN = 'https://redbrushtrailers.com';

    /**
     * @const string
     */
    const TEST_SESSION_ID = 'CT000000000000000001';

    /**
     * @const string
     */
    const TEST_SOURCE = 'Facebook';

    /**
     * @const int
     */
    const TEST_SALES_PERSON_ID = 102;

    /**
     * @const int
     */
    const TEST_ITEM_ID = 98179430;


    /**
     * @var LegacyMockInterface|LeadServiceInterface
     */
    private $leadServiceMock;

    /**
     * @var LegacyMockInterface|ADFServiceInterface
     */
    private $adfServiceMock;

    /**
     * @var LegacyMockInterface|InquiryEmailServiceInterface
     */
    private $inquiryEmailServiceMock;

    /**
     * @var LegacyMockInterface|LeadRepositoryInterface
     */
    private $leadRepositoryMock;

    /**
     * @var LegacyMockInterface|TrackingRepositoryInterface
     */
    private $trackingRepositoryMock;

    /**
     * @var LegacyMockInterface|TrackingUnitRepositoryInterface
     */
    private $trackingUnitRepositoryMock;


    /**
     * @var Lead
     */
    private $mergeLead;

    public function setUp(): void
    {
        parent::setUp();

        $this->leadServiceMock = Mockery::mock(LeadServiceInterface::class);
        $this->app->instance(LeadServiceInterface::class, $this->leadServiceMock);

        $this->inquiryEmailServiceMock = Mockery::mock(InquiryEmailServiceInterface::class);
        $this->app->instance(InquiryEmailServiceInterface::class, $this->inquiryEmailServiceMock);

        $this->adfServiceMock = Mockery::mock(ADFServiceInterface::class);
        $this->app->instance(ADFServiceInterface::class, $this->adfServiceMock);

        $this->leadRepositoryMock = Mockery::mock(LeadRepositoryInterface::class);
        $this->app->instance(LeadRepositoryInterface::class, $this->leadRepositoryMock);

        $this->trackingRepositoryMock = Mockery::mock(TrackingRepositoryInterface::class);
        $this->app->instance(TrackingRepositoryInterface::class, $this->trackingRepositoryMock);

        $this->trackingUnitRepositoryMock = Mockery::mock(TrackingUnitRepositoryInterface::class);
        $this->app->instance(TrackingUnitRepositoryInterface::class, $this->trackingUnitRepositoryMock);
    }


    /**
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreate()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_GENERAL
        ]);
        $lead->identifier = self::TEST_ITEM_ID;

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $lead->dealer_id,
            'website_id' => $lead->website_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[0],
            'lead_types' => [$lead->lead_type],
            'device' => self::TEST_DEVICE,
            'title' => $lead->title,
            'url' => $lead->referral,
            'referral' => $lead->referral,
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
            'metadata' => $lead->metadata,
            'is_spam' => 0,
            'lead_source' => self::TEST_SOURCE,
            'lead_status' => LeadStatus::STATUS_MEDIUM,
            'contact_type' => LeadStatus::TYPE_CONTACT,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = $this->prepareInquiryLead($sendRequestParams);


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
            ->never();

        // Mock Lead Repository
        $this->leadRepositoryMock
            ->shouldReceive('findAllMatches')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn(new Collection());

        // Mock Create Lead
        $this->leadServiceMock
            ->shouldReceive('create')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($lead);

        // Mock ADF Export
        $this->adfServiceMock
            ->shouldReceive('export')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, self::TEST_ITEM_ID);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class, AutoResponderJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result->full_name, $lead->full_name);
        $this->assertSame($result->email_address, $lead->email_address);
        $this->assertSame($result->phone_number, $lead->phone_number);
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

        // Get Test Lead
        $lead = factory(Lead::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_GENERAL
        ]);
        $lead->identifier = self::TEST_ITEM_ID;

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $lead->dealer_id,
            'website_id' => $lead->website_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[0],
            'lead_types' => [$lead->lead_type],
            'device' => self::TEST_DEVICE,
            'title' => $lead->title,
            'url' => $lead->referral,
            'referral' => $lead->referral,
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
            'metadata' => $lead->metadata,
            'is_spam' => 0,
            'lead_source' => self::TEST_SOURCE,
            'lead_status' => LeadStatus::STATUS_MEDIUM,
            'contact_type' => LeadStatus::TYPE_CONTACT,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = $this->prepareInquiryLead($sendRequestParams);


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

        // Mock Lead Repository
        $this->leadRepositoryMock
            ->shouldReceive('findAllMatches')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn(new Collection());

        // Mock Create Lead
        $this->leadServiceMock
            ->shouldReceive('create')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($lead);

        // Mock ADF Export
        $this->adfServiceMock
            ->shouldReceive('export')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, self::TEST_ITEM_ID);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class, AutoResponderJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result->full_name, $lead->full_name);
        $this->assertSame($result->email_address, $lead->email_address);
        $this->assertSame($result->phone_number, $lead->phone_number);
    }

    /**
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendInventory()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => self::TEST_ITEM_ID,
            'lead_type' => LeadType::TYPE_INVENTORY
        ]);
        $lead->identifier = self::TEST_ITEM_ID;

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $lead->dealer_id,
            'website_id' => $lead->website_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[2],
            'lead_types' => [$lead->lead_type],
            'item_id' => $lead->inventory_id,
            'device' => self::TEST_DEVICE,
            'title' => $lead->title,
            'url' => $lead->referral,
            'referral' => $lead->referral,
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
            'metadata' => $lead->metadata,
            'is_spam' => 0,
            'lead_source' => self::TEST_SOURCE,
            'lead_status' => LeadStatus::STATUS_MEDIUM,
            'contact_type' => LeadStatus::TYPE_CONTACT,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [$sendRequestParams['item_id']];

        // Get Inquiry Lead
        $inquiry = $this->prepareInquiryLead($sendRequestParams);


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

        // Mock Lead Repository
        $this->leadRepositoryMock
            ->shouldReceive('findAllMatches')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn(new Collection());

        // Mock Create Lead
        $this->leadServiceMock
            ->shouldReceive('create')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($lead);

        // Mock ADF Export
        $this->adfServiceMock
            ->shouldReceive('export')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, self::TEST_ITEM_ID);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->once()
            ->with($inquiry->cookieSessionId, $inquiry->itemId, 'inventory');

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class, AutoResponderJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result->full_name, $lead->full_name);
        $this->assertSame($result->email_address, $lead->email_address);
        $this->assertSame($result->phone_number, $lead->phone_number);
    }

    /**
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendPart()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_INVENTORY
        ]);
        $lead->identifier = self::TEST_ITEM_ID;

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $lead->dealer_id,
            'website_id' => $lead->website_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[3],
            'lead_types' => [$lead->lead_type],
            'item_id' => self::TEST_ITEM_ID,
            'device' => self::TEST_DEVICE,
            'title' => $lead->title,
            'url' => $lead->referral,
            'referral' => $lead->referral,
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
            'metadata' => $lead->metadata,
            'is_spam' => 0,
            'lead_source' => self::TEST_SOURCE,
            'lead_status' => LeadStatus::STATUS_MEDIUM,
            'contact_type' => LeadStatus::TYPE_CONTACT,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = $this->prepareInquiryLead($sendRequestParams);


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

        // Mock Lead Repository
        $this->leadRepositoryMock
            ->shouldReceive('findAllMatches')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn(new Collection());

        // Mock Create Lead
        $this->leadServiceMock
            ->shouldReceive('create')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($lead);

        // Mock ADF Export
        $this->adfServiceMock
            ->shouldReceive('export')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, self::TEST_ITEM_ID);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->once()
            ->with($inquiry->cookieSessionId, $inquiry->itemId, 'part');

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class, AutoResponderJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result->full_name, $lead->full_name);
        $this->assertSame($result->email_address, $lead->email_address);
        $this->assertSame($result->phone_number, $lead->phone_number);
    }

    /**
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendNoAutoAssign()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_INVENTORY
        ]);
        $lead->identifier = self::TEST_ITEM_ID;

        // Generate Lead Status
        $leadStatus = factory(LeadStatus::class)->make([
            'dealer_id' => self::getTestDealerId(),
            'tc_lead_identifier' => $lead->identifier,
            'sales_person_id' => self::TEST_SALES_PERSON_ID
        ]);
        $lead->setRelation('leadStatus', $leadStatus);

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $lead->dealer_id,
            'website_id' => $lead->website_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[1],
            'lead_types' => [$lead->lead_type],
            'device' => self::TEST_DEVICE,
            'title' => $lead->title,
            'url' => $lead->referral,
            'referral' => $lead->referral,
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
            'metadata' => $lead->metadata,
            'is_spam' => 0,
            'lead_source' => self::TEST_SOURCE,
            'lead_status' => LeadStatus::STATUS_MEDIUM,
            'contact_type' => LeadStatus::TYPE_CONTACT,
            'sales_person_id' => self::TEST_SALES_PERSON_ID,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = $this->prepareInquiryLead($sendRequestParams);


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

        // Mock Lead Repository
        $this->leadRepositoryMock
            ->shouldReceive('findAllMatches')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn(new Collection());

        // Mock Create Lead
        $this->leadServiceMock
            ->shouldReceive('create')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($lead);

        // Mock ADF Export
        $this->adfServiceMock
            ->shouldReceive('export')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, self::TEST_ITEM_ID);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Expects Auto Responder Job ONLY
        $this->expectsJobs([AutoResponderJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result->full_name, $lead->full_name);
        $this->assertSame($result->email_address, $lead->email_address);
        $this->assertSame($result->phone_number, $lead->phone_number);
    }

    /**
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendMerge()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_GENERAL
        ]);
        $lead->identifier = self::TEST_ITEM_ID;

        // Get Matches
        $matches = $this->getMatchingLeads($lead);

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $lead->dealer_id,
            'website_id' => $lead->website_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[1],
            'lead_types' => [$lead->lead_type],
            'device' => self::TEST_DEVICE,
            'title' => $lead->title,
            'url' => $lead->referral,
            'referral' => $lead->referral,
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
            'metadata' => $lead->metadata,
            'is_spam' => 0,
            'lead_source' => self::TEST_SOURCE,
            'lead_status' => LeadStatus::STATUS_MEDIUM,
            'contact_type' => LeadStatus::TYPE_CONTACT,
            'sales_person_id' => self::TEST_SALES_PERSON_ID,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = $this->prepareInquiryLead($sendRequestParams);


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

        // Mock Lead Repository
        $this->leadRepositoryMock
            ->shouldReceive('findAllMatches')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($matches);

        // Mock Create Lead
        $this->leadServiceMock
            ->shouldReceive('merge')
            ->once()
            ->andReturn($this->mergeLead);

        // Mock ADF Export
        $this->adfServiceMock
            ->shouldReceive('export')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, $this->mergeLead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Expects Auto Responder Job ONLY
        $this->expectsJobs([AutoResponderJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Merged Lead Details
        $this->assertSame($result->identifier, $this->mergeLead->identifier);
    }

    /**
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendMergeExactMatch()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_GENERAL
        ]);
        $lead->identifier = self::TEST_ITEM_ID;

        // Get Matches
        $matches = $this->getMatchingLeads($lead, true);

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $lead->dealer_id,
            'website_id' => $lead->website_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[1],
            'lead_types' => [$lead->lead_type],
            'device' => self::TEST_DEVICE,
            'title' => $lead->title,
            'url' => $lead->referral,
            'referral' => $lead->referral,
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
            'metadata' => $lead->metadata,
            'is_spam' => 0,
            'lead_source' => self::TEST_SOURCE,
            'lead_status' => LeadStatus::STATUS_MEDIUM,
            'contact_type' => LeadStatus::TYPE_CONTACT,
            'sales_person_id' => self::TEST_SALES_PERSON_ID,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = $this->prepareInquiryLead($sendRequestParams);


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

        // Mock Lead Repository
        $this->leadRepositoryMock
            ->shouldReceive('findAllMatches')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($matches);

        // Mock Create Lead
        $this->leadServiceMock
            ->shouldReceive('merge')
            ->once()
            ->andReturn($this->mergeLead);

        // Mock ADF Export
        $this->adfServiceMock
            ->shouldReceive('export')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, $this->mergeLead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Expects Auto Responder Job ONLY
        $this->expectsJobs([AutoResponderJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Merged Lead Details
        $this->assertSame($result->identifier, $this->mergeLead->identifier);
        $this->assertSame($result->first_name, $this->mergeLead->first_name);
        $this->assertSame($result->last_name, $this->mergeLead->last_name);
        $this->assertSame($result->phone_number, $this->mergeLead->phone_number);
        $this->assertSame($result->email_address, $this->mergeLead->email_address);
    }

    /**
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendMergeFinancing()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_FINANCING
        ]);
        $lead->identifier = self::TEST_ITEM_ID;

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $lead->dealer_id,
            'website_id' => $lead->website_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[1],
            'lead_types' => [$lead->lead_type],
            'device' => self::TEST_DEVICE,
            'title' => $lead->title,
            'url' => $lead->referral,
            'referral' => $lead->referral,
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
            'metadata' => $lead->metadata,
            'is_spam' => 0,
            'lead_source' => self::TEST_SOURCE,
            'lead_status' => LeadStatus::STATUS_MEDIUM,
            'contact_type' => LeadStatus::TYPE_CONTACT,
            'sales_person_id' => self::TEST_SALES_PERSON_ID,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = $this->prepareInquiryLead($sendRequestParams);


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

        // Mock Lead Repository
        $this->leadRepositoryMock
            ->shouldReceive('findAllMatches')
            ->never();

        // Mock Create Lead
        $this->leadServiceMock
            ->shouldReceive('create')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($lead);

        // Mock ADF Export Repository
        $this->adfServiceMock
            ->shouldReceive('export')
            ->never();

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Expects Auto Responder Job ONLY
        $this->expectsJobs([AutoResponderJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result->full_name, $lead->full_name);
        $this->assertSame($result->email_address, $lead->email_address);
        $this->assertSame($result->phone_number, $lead->phone_number);
    }


    /**
     * Prepare Inquiry Lead
     * 
     * @param array $params
     * @return InquiryLead
     */
    private function prepareInquiryLead(array $params) {
        // Set Website Domain
        $params['website_domain'] = self::TEST_DOMAIN;

        // Get Inquiry From Details For Website
        $config = self::TEST_WEBSITE_CONFIG;
        $params['logo'] = $config['logo'];
        $params['logo_url'] = $config['logoUrl'];
        $params['from_name'] = $config['fromName'];

        // Get Inquiry Name/Email
        $params['inquiry_name'] = self::TEST_INQUIRY_NAME;
        $params['inquiry_email'] = self::TEST_INQUIRY_EMAIL;

        // Get Data By Inquiry Type
        $vars = $this->getInquiryTypeVars($params);

        // Create Inquiry Lead
        return new InquiryLead($vars);
    }

    /**
     * Get Inquiry Type Specific Vars
     * 
     * @param array $params
     * @return array_merge($params, array{'stock': string,
     *                                    'title': string})
     */
    private function getInquiryTypeVars(array $params): array {
        // Toggle Inquiry Type
        switch($params['inquiry_type']) {
            case "inventory":
            case "bestprice":
                $params['stock'] = 'TESTTRADEIN9995';
                $params['title'] = '2020 4-Star Trailers Denali  Popup Camper';
            break;
            case "part":
                $params['stock'] = 'cleaner-54321';
                $params['title'] = 'Cleaner 54321';
            break;
            case "showroom":
                $params['title'] = '2016 Winnebago Sightseer 33C';
            break;
        }

        // Return Updated Params Array
        return $params;
    }


    /**
     * Get Matching Leads
     * 
     * @param Lead $lead
     * @param bool $includeExact
     * @return Collection<Lead>
     */
    private function getMatchingLeads(Lead $lead, bool $includeExact = false) {
        // Create Seeds
        $seeds = [
            ['firstname' => $lead->first_name, 'lastname' => $lead->last_name],
            ['primary' => 1, 'firstname' => $lead->first_name, 'phone' => $lead->phone_number, 'email' => $lead->email_address],
            ['phone' => $lead->phone_number],
            ['firstname' => $lead->first_name, 'lastname' => $lead->last_name, 'email' => $lead->email_address],
            ['lastname' => $lead->last_name],
            ['phone' => $lead->phone_number, 'email' => $lead->email_address],
        ];

        // Replace Primary With EXACT
        if($includeExact) {
            unset($seeds[1]['primary']);
            $seeds[] = [
                'primary' => 1,
                'firstname' => $lead->first_name,
                'lastname' => $lead->last_name,
                'phone' => $lead->phone_number,
                'email' => $lead->email_address
            ];
        }

        // Create Matching Leads
        $matches = new Collection();
        collect($seeds)->each(function (array $seed) use(&$matches, $lead): void {
            $match = factory(Lead::class)->make([
                'dealer_id' => $lead->dealer_id,
                'dealer_location_id' => $lead->dealer_location_id,
                'website_id' => $lead->website_id,
                'inventory_id' => 0,
                'lead_type' => LeadType::TYPE_GENERAL,
                'first_name' => $seed['firstname'] ?? null,
                'last_name' => $seed['lastname'] ?? null,
                'email_address' => $seed['email'] ?? null,
                'phone_number' => $seed['phone'] ?? null
            ]);
            $match->identifier = $lead->identifier + $matches->count() + 1;

            // Add Matches
            if(!empty($seed['primary'])) {
                $this->mergeLead = $match;
            }
            $matches->push($match);
        });

        // Return Matches Collection
        return $matches;
    }
}