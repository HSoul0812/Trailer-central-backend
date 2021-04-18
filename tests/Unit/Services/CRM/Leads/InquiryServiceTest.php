<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Jobs\CRM\Leads\AutoAssignJob;
use App\Jobs\Email\AutoResponderJob;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Interactions\Interaction;
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
     * @group Inquiry
     *
     * @throws BindingResolutionException
     */
    public function testCreate()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;

        // Send Request Params
        $sendRequestParams = [
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
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();

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
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->once()
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->once()
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->once()
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->once()
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class, AutoResponderJob::class]);

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
     * @covers ::send
     * @group Inquiry
     *
     * @throws BindingResolutionException
     */
    public function testSend()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;

        // Send Request Params
        $sendRequestParams = [
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
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();

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
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->once()
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->once()
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->once()
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->once()
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class, AutoResponderJob::class]);

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
     * @covers ::send
     * @group Inquiry
     *
     * @throws BindingResolutionException
     */
    public function testSendInventory()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;

        // Send Request Params
        $sendRequestParams = [
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
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();

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
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->once()
            ->with($inquiry->cookieSessionId, $inquiry->itemId, 'inventory');

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->once()
             ->andReturn([LeadType::TYPE_INVENTORY]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->once()
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->once()
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->once()
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class, AutoResponderJob::class]);

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
     * @covers ::send
     * @group Inquiry
     *
     * @throws BindingResolutionException
     */
    public function testSendPart()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;

        // Send Request Params
        $sendRequestParams = [
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
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();

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
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->once()
            ->with($inquiry->cookieSessionId, $inquiry->itemId, 'part');

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->once()
             ->andReturn([LeadType::TYPE_INVENTORY]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->once()
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->once()
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->once()
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class, AutoResponderJob::class]);

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
     * @covers ::send
     * @group Inquiry
     *
     * @throws BindingResolutionException
     */
    public function testSendShowroom()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;

        // Send Request Params
        $sendRequestParams = [
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
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();

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
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->once()
            ->with($inquiry->cookieSessionId, $inquiry->itemId, 'showroom');

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->once()
             ->andReturn([LeadType::TYPE_SHOWROOM_MODEL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->once()
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->once()
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->once()
             ->andReturn(null);

        // Expects Auto Assign/Auto Responder Jobs
        $this->expectsJobs([AutoAssignJob::class, AutoResponderJob::class]);

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
     * @covers ::send
     * @group Inquiry
     *
     * @throws BindingResolutionException
     */
    public function testSendNoAutoAssign()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $status->sales_person_id = 1;
        $lead->leadStatus = $status;

        // Send Request Params
        $sendRequestParams = [
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
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();

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
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->once()
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->once()
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->once()
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->once()
             ->andReturn(null);

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
     * @group Inquiry
     *
     * @throws BindingResolutionException
     */
    public function testSendMerge()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;

        $interaction = $this->getEloquentMock(Interaction::class);
        $interaction->interaction_id = 1;

        // Send Request Params
        $sendRequestParams = [
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


        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();
        $lead->shouldReceive('newDealerUser')->passthru();


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
            ->andReturn($interaction);

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

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->once()
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->once()
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->once()
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->once()
             ->andReturn(null);

        // Expects Auto Responder Job ONLY
        $this->expectsJobs([AutoResponderJob::class]);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Match Merged Lead Details
        $this->assertSame($result['data']['id'], $this->mergeLead->identifier);
        $this->assertSame($result['merge']['id'], $interaction->interaction_id);
    }

    /**
     * @covers ::send
     * @group Inquiry
     *
     * @throws BindingResolutionException
     */
    public function testSendMergeExactMatch()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;

        $interaction = $this->getEloquentMock(Interaction::class);
        $interaction->interaction_id = 1;

        // Send Request Params
        $sendRequestParams = [
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
            ->andReturn($interaction);

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

        // Mock Lead
        $this->mergeLead
            ->shouldReceive('getFullNameAttribute')
            ->andReturn($this->mergeLead->first_name . ' ' . $this->mergeLead->last_name);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->once()
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->once()
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->once()
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->once()
             ->andReturn(null);

        // Expects Auto Responder Job ONLY
        $this->expectsJobs([AutoResponderJob::class]);

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
     * @covers ::send
     * @group Inquiry
     *
     * @throws BindingResolutionException
     */
    public function testSendMergeFinancing()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $lead->leadStatus = $status;

        // Send Request Params
        $sendRequestParams = [
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

        // Mock Lead
        $lead->shouldReceive('getFullNameAttribute')
            ->andReturn($lead->first_name . ' ' . $lead->last_name);

        // Get Lead Types
        $lead->shouldReceive('getLeadTypesAttribute')
             ->once()
             ->andReturn([LeadType::TYPE_GENERAL]);

        // Get Full Address
        $lead->shouldReceive('getFullAddressAttribute')
             ->once()
             ->andReturn('');

        // Get Pretty Phone
        $lead->shouldReceive('getPrettyPhoneNumberAttribute')
             ->once()
             ->andReturn($lead->phone_number);

        // Get Pretty Phone
        $lead->shouldReceive('getPreferredDealerLocationAttribute')
             ->once()
             ->andReturn(null);

        // Expects Auto Responder Job ONLY
        $this->expectsJobs([AutoResponderJob::class]);

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