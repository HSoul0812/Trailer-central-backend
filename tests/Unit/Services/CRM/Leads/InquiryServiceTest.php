<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Jobs\CRM\Leads\AutoAssignJob;
use App\Jobs\Email\AutoResponderJob;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\CRM\Leads\InquiryServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
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

        // Send Request Params
        $sendRequestParams = [
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[0],
            'lead_types' => [LeadType::TYPE_GENERAL],
            'device' => self::TEST_DEVICE,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone_number' => $lead->phone_number,
            'email_address' => $lead->email_address,
            'is_spam' => 0,
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
    /*public function testSendInventory()
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
    }*/

    /**
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    /*public function testSendPart()
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
    }*/

    /**
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    /*public function testSendNoAutoAssign()
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
    }*/


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
}
