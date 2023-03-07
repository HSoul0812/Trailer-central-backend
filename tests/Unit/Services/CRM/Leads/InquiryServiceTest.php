<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Jobs\CRM\Leads\AutoAssignJob;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\User\SalesPerson;
use App\Models\Website\Config\WebsiteConfig;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Services\CRM\Leads\Export\ADFServiceInterface;
use App\Services\CRM\Leads\Export\IDSServiceInterface;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\CRM\Leads\InquiryServiceInterface;
use App\Services\Website\WebsiteConfigServiceInterface;
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
    const TEST_FIRST_NAME = 'Alegra';
    const TEST_LAST_NAME = 'Johnson';
    const TEST_PHONE = '555-555-5555';
    const TEST_EMAIL = 'alegra@nowhere.com';


    /**
     * @var LegacyMockInterface|LeadServiceInterface
     */
    private $leadServiceMock;

    /**
     * @var LegacyMockInterface|ADFServiceInterface
     */
    private $adfServiceMock;

    /**
     * @var LegacyMockInterface|IDSServiceInterface
     */
    private $idsServiceMock;

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
     * @var LegacyMockInterface|UserRepositoryInterface
     */
    private $userRepositoryMock;

    /**
     * @var LegacyMockInterface|WebsiteConfigServiceInterface
     */
    private $websiteConfigServiceMock;


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

        $this->idsServiceMock = Mockery::mock(IDSServiceInterface::class);
        $this->app->instance(IDSServiceInterface::class, $this->idsServiceMock);

        $this->leadRepositoryMock = Mockery::mock(LeadRepositoryInterface::class);
        $this->app->instance(LeadRepositoryInterface::class, $this->leadRepositoryMock);

        $this->trackingRepositoryMock = Mockery::mock(TrackingRepositoryInterface::class);
        $this->app->instance(TrackingRepositoryInterface::class, $this->trackingRepositoryMock);

        $this->trackingUnitRepositoryMock = Mockery::mock(TrackingUnitRepositoryInterface::class);
        $this->app->instance(TrackingUnitRepositoryInterface::class, $this->trackingUnitRepositoryMock);

        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepositoryMock);

        $this->websiteConfigServiceMock = Mockery::mock(WebsiteConfigServiceInterface::class);
        $this->app->instance(WebsiteConfigServiceInterface::class, $this->websiteConfigServiceMock);
    }


    /**
     * @group CRM
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreate()
    {
        // Mock Website
        $website = $this->getEloquentMock(Website::class);
        $website->id = 1;
        $website->dealer_id = 1;
        $website->domain = self::TEST_DOMAIN;

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->isCrmActive = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->dealer_id = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;
        $lead->units = new Collection();

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $dealer->dealer_id,
            'website_id' => $website->id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[0],
            'lead_types' => [LeadType::TYPE_GENERAL],
            'device' => self::TEST_DEVICE,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone_number' => $lead->phone_number,
            'email_address' => $lead->email_address,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = new InquiryLead($sendRequestParams);


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('belongsToMany')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();
        $lead->shouldReceive('units')->passthru();

        // @var InquiryServiceInterface $service
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

        // Mock User Repository
        $this->userRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealer->dealer_id])
            ->andReturn($dealer);

        // Mock Website Config Service
        $this->websiteConfigServiceMock
            ->shouldReceive('getConfigByWebsite')
            ->once()
            ->with($website->id, WebsiteConfig::LEADS_MERGE_ENABLED)
            ->andReturn([WebsiteConfig::LEADS_MERGE_ENABLED => '1']);

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

        // Mock IDS Export
        $this->idsServiceMock
            ->shouldReceive('exportInquiry')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Inventory ID's
        $lead->shouldReceive('getInventoryIdsAttribute')
             ->andReturn($sendInquiryParams['inventory']);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->create($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result['data']['name'], $lead->full_name);
        $this->assertSame($result['data']['email'], $lead->email_address);
        $this->assertSame($result['data']['phone'], $lead->phone_number);
    }


    /**
     * @group CRM
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSend()
    {
        // Mock Website
        $website = $this->getEloquentMock(Website::class);
        $website->id = 1;
        $website->dealer_id = 1;
        $website->domain = self::TEST_DOMAIN;

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->isCrmActive = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->dealer_id = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;
        $lead->units = new Collection();

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $dealer->dealer_id,
            'website_id' => $website->id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[0],
            'lead_types' => [LeadType::TYPE_GENERAL],
            'device' => self::TEST_DEVICE,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone_number' => $lead->phone_number,
            'email_address' => $lead->email_address,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = new InquiryLead($sendRequestParams);


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('belongsToMany')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();
        $lead->shouldReceive('units')->passthru();

        // @var InquiryServiceInterface $service
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

        // Mock User Repository
        $this->userRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealer->dealer_id])
            ->andReturn($dealer);

        // Mock Website Config Service
        $this->websiteConfigServiceMock
            ->shouldReceive('getConfigByWebsite')
            ->once()
            ->with($website->id, WebsiteConfig::LEADS_MERGE_ENABLED)
            ->andReturn([WebsiteConfig::LEADS_MERGE_ENABLED => '1']);

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

        // Mock IDS Export
        $this->idsServiceMock
            ->shouldReceive('exportInquiry')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Inventory ID's
        $lead->shouldReceive('getInventoryIdsAttribute')
             ->andReturn($sendInquiryParams['inventory']);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result['data']['name'], $lead->full_name);
        $this->assertSame($result['data']['email'], $lead->email_address);
        $this->assertSame($result['data']['phone'], $lead->phone_number);
    }

    /**
     * @group CRM
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendInventory()
    {
        // Mock Website
        $website = $this->getEloquentMock(Website::class);
        $website->id = 1;
        $website->dealer_id = 1;
        $website->domain = self::TEST_DOMAIN;

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->isCrmActive = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->dealer_id = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;
        $lead->units = new Collection();

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $dealer->dealer_id,
            'website_id' => $website->id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[2],
            'lead_types' => [LeadType::TYPE_INVENTORY],
            'device' => self::TEST_DEVICE,
            'item_id' => 1,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone_number' => $lead->phone_number,
            'email_address' => $lead->email_address,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [$sendRequestParams['item_id']];

        // Get Inquiry Lead
        $inquiry = new InquiryLead($sendRequestParams);


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('belongsToMany')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();
        $lead->shouldReceive('units')->passthru();

        // @var InquiryServiceInterface $service
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

        // Mock User Repository
        $this->userRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealer->dealer_id])
            ->andReturn($dealer);

        // Mock Website Config Service
        $this->websiteConfigServiceMock
            ->shouldReceive('getConfigByWebsite')
            ->once()
            ->with($website->id, WebsiteConfig::LEADS_MERGE_ENABLED)
            ->andReturn([WebsiteConfig::LEADS_MERGE_ENABLED => '1']);

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

        // Mock IDS Export
        $this->idsServiceMock
            ->shouldReceive('exportInquiry')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->once()
            ->with($inquiry->cookieSessionId, $inquiry->itemId, 'inventory');

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Inventory ID's
        $lead->shouldReceive('getInventoryIdsAttribute')
             ->andReturn($sendInquiryParams['inventory']);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->andReturn([LeadType::TYPE_INVENTORY]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result['data']['name'], $lead->full_name);
        $this->assertSame($result['data']['email'], $lead->email_address);
        $this->assertSame($result['data']['phone'], $lead->phone_number);
    }

    /**
     * @group CRM
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendPart()
    {
        // Mock Website
        $website = $this->getEloquentMock(Website::class);
        $website->id = 1;
        $website->dealer_id = 1;
        $website->domain = self::TEST_DOMAIN;

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->isCrmActive = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->dealer_id = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;
        $lead->units = new Collection();

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $dealer->dealer_id,
            'website_id' => $website->id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[3],
            'lead_types' => [LeadType::TYPE_INVENTORY],
            'device' => self::TEST_DEVICE,
            'item_id' => 1,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone_number' => $lead->phone_number,
            'email_address' => $lead->email_address,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = new InquiryLead($sendRequestParams);


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('belongsToMany')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();
        $lead->shouldReceive('units')->passthru();

        // @var InquiryServiceInterface $service
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

        // Mock User Repository
        $this->userRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealer->dealer_id])
            ->andReturn($dealer);

        // Mock Website Config Service
        $this->websiteConfigServiceMock
            ->shouldReceive('getConfigByWebsite')
            ->once()
            ->with($website->id, WebsiteConfig::LEADS_MERGE_ENABLED)
            ->andReturn([WebsiteConfig::LEADS_MERGE_ENABLED => '1']);

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

        // Mock IDS Export
        $this->idsServiceMock
            ->shouldReceive('exportInquiry')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->once()
            ->with($inquiry->cookieSessionId, $inquiry->itemId, 'part');

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Inventory ID's
        $lead->shouldReceive('getInventoryIdsAttribute')
             ->andReturn($sendInquiryParams['inventory']);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->andReturn([LeadType::TYPE_INVENTORY]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result['data']['name'], $lead->full_name);
        $this->assertSame($result['data']['email'], $lead->email_address);
        $this->assertSame($result['data']['phone'], $lead->phone_number);
    }

    /**
     * @group CRM
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendShowroom()
    {
        // Mock Website
        $website = $this->getEloquentMock(Website::class);
        $website->id = 1;
        $website->dealer_id = 1;
        $website->domain = self::TEST_DOMAIN;

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->isCrmActive = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->dealer_id = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;
        $lead->units = new Collection();

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $dealer->dealer_id,
            'website_id' => $website->id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[4],
            'lead_types' => [LeadType::TYPE_SHOWROOM_MODEL],
            'device' => self::TEST_DEVICE,
            'item_id' => 1,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone_number' => $lead->phone_number,
            'email_address' => $lead->email_address,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = new InquiryLead($sendRequestParams);


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('belongsToMany')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();
        $lead->shouldReceive('units')->passthru();

        // @var InquiryServiceInterface $service
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

        // Mock User Repository
        $this->userRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealer->dealer_id])
            ->andReturn($dealer);

        // Mock Website Config Service
        $this->websiteConfigServiceMock
            ->shouldReceive('getConfigByWebsite')
            ->once()
            ->with($website->id, WebsiteConfig::LEADS_MERGE_ENABLED)
            ->andReturn([WebsiteConfig::LEADS_MERGE_ENABLED => '1']);

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

        // Mock IDS Export
        $this->idsServiceMock
            ->shouldReceive('exportInquiry')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->once()
            ->with($inquiry->cookieSessionId, $inquiry->itemId, 'showroom');

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Inventory ID's
        $lead->shouldReceive('getInventoryIdsAttribute')
             ->andReturn($sendInquiryParams['inventory']);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->andReturn([LeadType::TYPE_SHOWROOM_MODEL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result['data']['name'], $lead->full_name);
        $this->assertSame($result['data']['email'], $lead->email_address);
        $this->assertSame($result['data']['phone'], $lead->phone_number);
    }

    /**
     * @group CRM
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendNoAutoAssign()
    {
        // Mock Website
        $website = $this->getEloquentMock(Website::class);
        $website->id = 1;
        $website->dealer_id = 1;
        $website->domain = self::TEST_DOMAIN;

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->isCrmActive = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->dealer_id = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $status->sales_person_id = 1;
        $lead->leadStatus = $status;
        $lead->units = new Collection();

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $dealer->dealer_id,
            'website_id' => $website->id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[0],
            'lead_types' => [LeadType::TYPE_GENERAL],
            'device' => self::TEST_DEVICE,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone_number' => $lead->phone_number,
            'email_address' => $lead->email_address,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = new InquiryLead($sendRequestParams);


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('belongsToMany')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();
        $lead->shouldReceive('units')->passthru();

        // @var InquiryServiceInterface $service
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

        // Mock User Repository
        $this->userRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealer->dealer_id])
            ->andReturn($dealer);

        // Mock Website Config Service
        $this->websiteConfigServiceMock
            ->shouldReceive('getConfigByWebsite')
            ->once()
            ->with($website->id, WebsiteConfig::LEADS_MERGE_ENABLED)
            ->andReturn([WebsiteConfig::LEADS_MERGE_ENABLED => '1']);

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

        // Mock IDS Export
        $this->idsServiceMock
            ->shouldReceive('exportInquiry')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Inventory ID's
        $lead->shouldReceive('getInventoryIdsAttribute')
             ->andReturn($sendInquiryParams['inventory']);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->andReturn(null);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result['data']['name'], $lead->full_name);
        $this->assertSame($result['data']['email'], $lead->email_address);
        $this->assertSame($result['data']['phone'], $lead->phone_number);
    }


    /**
     * @group CRM
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendMerge()
    {
        // Mock Website
        $website = $this->getEloquentMock(Website::class);
        $website->id = 1;
        $website->dealer_id = 1;
        $website->domain = self::TEST_DOMAIN;

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->isCrmActive = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->dealer_id = 1;
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
        $sendRequestParams = [
            'dealer_id' => $dealer->dealer_id,
            'website_id' => $website->id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[0],
            'lead_types' => [LeadType::TYPE_GENERAL],
            'device' => self::TEST_DEVICE,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone_number' => $lead->phone_number,
            'email_address' => $lead->email_address,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = new InquiryLead($sendRequestParams);

        // Get Matches
        $matches = $this->getMatchingLeads($lead);
        $interaction->lead = $this->mergeLead;


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('belongsToMany')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();
        $lead->shouldReceive('units')->passthru();


        // @var InquiryServiceInterface $service
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

        // Mock User Repository
        $this->userRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealer->dealer_id])
            ->andReturn($dealer);

        // Mock Website Config Service
        $this->websiteConfigServiceMock
            ->shouldReceive('getConfigByWebsite')
            ->once()
            ->with($website->id, WebsiteConfig::LEADS_MERGE_ENABLED)
            ->andReturn([WebsiteConfig::LEADS_MERGE_ENABLED => '1']);

        // Mock Lead Repository
        $this->leadRepositoryMock
            ->shouldReceive('findAllMatches')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($matches);

        // Mock Merge Lead
        $this->leadServiceMock
            ->shouldReceive('mergeInquiry')
            ->once()
            ->andReturn($interaction);

        // Mock Update Lead
        $this->leadServiceMock
            ->shouldReceive('update')
            ->once()
            ->andReturn($this->mergeLead);

        // Mock ADF Export
        $this->adfServiceMock
            ->shouldReceive('export')
            ->once()
            ->andReturn(false);

        // Mock IDS Export
        $this->idsServiceMock
            ->shouldReceive('exportInquiry')
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

        // Mock Lead
        $this->mergeLead->shouldReceive('getFullNameAttribute')
            ->andReturn($this->mergeLead->first_name . ' ' . $this->mergeLead->last_name);

        // Get Inventory ID's
        $this->mergeLead->shouldReceive('getInventoryIdsAttribute')
             ->andReturn($sendInquiryParams['inventory']);

        // Get Lead Types
        $this->mergeLead->shouldReceive('getLeadTypesAttribute')
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $this->mergeLead->shouldReceive('getFullAddressAttribute')
             ->andReturn('');

        // Get Pretty Phone
        $this->mergeLead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->andReturn($this->mergeLead->phone_number);

        // Get Pretty Phone
        $this->mergeLead->shouldReceive('getPreferredDealerLocationAttribute')
             ->andReturn(null);

        // Get Real Username Attribute
        $interaction->shouldReceive('getRealUsernameAttribute')
             ->andReturn('');

        // Mock SalesPerson
        $salesPerson->shouldReceive('getFullNameAttribute')
            ->andReturn($salesPerson->first_name . ' ' . $salesPerson->last_name);

        // Mock SalesPerson Auth Config
        $salesPerson->shouldReceive('getAuthConfigAttribute')
            ->andReturn('');

        // Mock SalesPerson Auth Method
        $salesPerson->shouldReceive('getAuthMethodAttribute')
            ->andReturn('');

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Merged Lead Details
        $this->assertSame($result['data']['id'], $this->mergeLead->identifier);
        $this->assertSame($result['merge']['id'], $interaction->interaction_id);
    }

    /**
     * @group CRM
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendMergeExactMatch()
    {
        // Mock Website
        $website = $this->getEloquentMock(Website::class);
        $website->id = 1;
        $website->dealer_id = 1;
        $website->domain = self::TEST_DOMAIN;

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->isCrmActive = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->dealer_id = 1;
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
        $sendRequestParams = [
            'dealer_id' => $dealer->dealer_id,
            'website_id' => $website->id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[0],
            'lead_types' => [LeadType::TYPE_GENERAL],
            'device' => self::TEST_DEVICE,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone_number' => $lead->phone_number,
            'email_address' => $lead->email_address,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = new InquiryLead($sendRequestParams);

        // Get Matches
        $matches = $this->getMatchingLeads($lead, true);
        $interaction->lead = $this->mergeLead;


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('belongsToMany')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();
        $lead->shouldReceive('units')->passthru();


        // @var InquiryServiceInterface $service
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

        // Mock User Repository
        $this->userRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealer->dealer_id])
            ->andReturn($dealer);

        // Mock Website Config Service
        $this->websiteConfigServiceMock
            ->shouldReceive('getConfigByWebsite')
            ->once()
            ->with($website->id, WebsiteConfig::LEADS_MERGE_ENABLED)
            ->andReturn([WebsiteConfig::LEADS_MERGE_ENABLED => '1']);

        // Mock Lead Repository
        $this->leadRepositoryMock
            ->shouldReceive('findAllMatches')
            ->once()
            ->with($sendInquiryParams)
            ->andReturn($matches);

        // Mock Merge Lead
        $this->leadServiceMock
            ->shouldReceive('mergeInquiry')
            ->once()
            ->andReturn($interaction);

        // Mock Update Lead
        $this->leadServiceMock
            ->shouldReceive('update')
            ->once()
            ->andReturn($this->mergeLead);

        // Mock ADF Export
        $this->adfServiceMock
            ->shouldReceive('export')
            ->once()
            ->andReturn(false);

        // Mock IDS Export
        $this->idsServiceMock
            ->shouldReceive('exportInquiry')
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

        // Mock Lead
        $this->mergeLead->shouldReceive('getFullNameAttribute')
            ->andReturn($this->mergeLead->first_name . ' ' . $this->mergeLead->last_name);

        // Get Inventory ID's
        $this->mergeLead->shouldReceive('getInventoryIdsAttribute')
             ->andReturn($sendInquiryParams['inventory']);

        // Get Lead Types
        $this->mergeLead->shouldReceive('getLeadTypesAttribute')
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $this->mergeLead->shouldReceive('getFullAddressAttribute')
             ->andReturn('');

        // Get Pretty Phone
        $this->mergeLead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->andReturn($this->mergeLead->phone_number);

        // Get Pretty Phone
        $this->mergeLead->shouldReceive('getPreferredDealerLocationAttribute')
             ->andReturn(null);

        // Get Real Username Attribute
        $interaction->shouldReceive('getRealUsernameAttribute')
             ->andReturn('');

        // Mock SalesPerson
        $salesPerson->shouldReceive('getFullNameAttribute')
            ->andReturn($salesPerson->first_name . ' ' . $salesPerson->last_name);

        // Mock SalesPerson Auth Config
        $salesPerson->shouldReceive('getAuthConfigAttribute')
            ->andReturn('');

        // Mock SalesPerson Auth Method
        $salesPerson->shouldReceive('getAuthMethodAttribute')
            ->andReturn('');

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Merged Lead Details
        $this->assertSame($result['data']['id'], $this->mergeLead->identifier);
        $this->assertSame($result['data']['name'], $this->mergeLead->full_name);
        $this->assertSame($result['data']['phone'], $this->mergeLead->phone_number);
        $this->assertSame($result['data']['email'], $this->mergeLead->email_address);
        $this->assertSame($result['merge']['id'], $interaction->interaction_id);
    }

    /**
     * @group CRM
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendMergeFinancing()
    {
        // Mock Website
        $website = $this->getEloquentMock(Website::class);
        $website->id = 1;
        $website->dealer_id = 1;
        $website->domain = self::TEST_DOMAIN;

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->isCrmActive = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->dealer_id = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;
        $lead->units = new Collection();

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $dealer->dealer_id,
            'website_id' => $website->id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[0],
            'lead_types' => [LeadType::TYPE_FINANCING],
            'device' => self::TEST_DEVICE,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone_number' => $lead->phone_number,
            'email_address' => $lead->email_address,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = new InquiryLead($sendRequestParams);


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('belongsToMany')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();
        $lead->shouldReceive('units')->passthru();


        // @var InquiryServiceInterface $service
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

        // Mock User Repository
        $this->userRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealer->dealer_id])
            ->andReturn($dealer);

        // Mock Website Config Service
        $this->websiteConfigServiceMock
            ->shouldReceive('getConfigByWebsite')
            ->never();

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

        // Mock IDS Export
        $this->idsServiceMock
            ->shouldReceive('exportInquiry')
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

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Inventory ID's
        $lead->shouldReceive('getInventoryIdsAttribute')
             ->andReturn($sendInquiryParams['inventory']);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result['data']['name'], $lead->full_name);
        $this->assertSame($result['data']['email'], $lead->email_address);
        $this->assertSame($result['data']['phone'], $lead->phone_number);
        $this->assertNull($result['merge']);
    }

    /**
     * @group CRM
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendMergeDisabled()
    {
        // Mock Website
        $website = $this->getEloquentMock(Website::class);
        $website->id = 1;
        $website->dealer_id = 1;
        $website->domain = self::TEST_DOMAIN;

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->isCrmActive = 1;

        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->dealer_id = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;
        $lead->units = new Collection();

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $dealer->dealer_id,
            'website_id' => $website->id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[0],
            'lead_types' => [LeadType::TYPE_GENERAL],
            'device' => self::TEST_DEVICE,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone_number' => $lead->phone_number,
            'email_address' => $lead->email_address,
            'cookie_session_id' => self::TEST_SESSION_ID
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];

        // Get Inquiry Lead
        $inquiry = new InquiryLead($sendRequestParams);


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('belongsToMany')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();
        $lead->shouldReceive('units')->passthru();


        // @var InquiryServiceInterface $service
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

        // Mock User Repository
        $this->userRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealer->dealer_id])
            ->andReturn($dealer);

        // Mock Website Config Service
        $this->websiteConfigServiceMock
            ->shouldReceive('getConfigByWebsite')
            ->once()
            ->with($website->id, WebsiteConfig::LEADS_MERGE_ENABLED)
            ->andReturn([WebsiteConfig::LEADS_MERGE_ENABLED => '0']);

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

        // Mock ADF Export
        $this->adfServiceMock
            ->shouldReceive('export')
            ->once()
            ->andReturn(false);

        // Mock IDS Export
        $this->idsServiceMock
            ->shouldReceive('exportInquiry')
            ->once()
            ->andReturn(false);

        // Mock Sales Person Repository
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Inventory ID's
        $lead->shouldReceive('getInventoryIdsAttribute')
             ->andReturn($sendInquiryParams['inventory']);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Lead Details
        $this->assertSame($result['data']['name'], $lead->full_name);
        $this->assertSame($result['data']['email'], $lead->email_address);
        $this->assertSame($result['data']['phone'], $lead->phone_number);
        $this->assertNull($result['merge']);
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
            $match = $this->getEloquentMock(Lead::class);
            $match->first_name = $seed['firstname'] ?? null;
            $match->last_name = $seed['lastname'] ?? null;
            $match->email_address = $seed['email'] ?? null;
            $match->phone_number = $seed['phone'] ?? null;
            $match->identifier = $lead->identifier + $matches->count() + 1;

            $status = $this->getEloquentMock(LeadStatus::class);
            $match->leadStatus = $status;
            $match->units = new Collection();

            // Get Clean Phone
            $phone = preg_replace("/[-+)( x]+/", "", $match->phone_number);
            $match->shouldReceive('getCleanPhoneAttribute')
                  ->twice()
                  ->andReturn(((strlen($phone) === 11) ? $phone : '1' . $phone));

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