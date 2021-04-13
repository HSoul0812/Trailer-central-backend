<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Mail\InquiryEmail;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Models\Inventory\Inventory;
use App\Models\Parts\Part;
use App\Models\Showroom\Showroom;
use App\Models\Website\Website;
use App\Models\Website\Tracking\Tracking;
use App\Models\Website\Tracking\TrackingUnit;
use App\Models\User\DealerLocation;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\CRM\Leads\InquiryServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
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
        $website = Website::find($websiteId);

        // Create Dummy Inventory
        $inventory = factory(Inventory::class)->create([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId
        ]);

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'website_id' => $websiteId,
            'inventory_id' => $inventory->inventory_id,
            'lead_type' => LeadType::TYPE_GENERAL
        ]);
        $status = factory(LeadStatus::class)->create([
            'tc_lead_identifier' => $lead->identifier
        ]);

        // Get Tracking Details
        $tracking = factory(Tracking::class)->create([
            'domain' => $website->domain
        ]);
        $trackingUnits = $this->createTrackingUnits($dealerId, $tracking->session_id);

        // Send Request Params
        $sendRequestParams = [
            'dealer_id' => $lead->dealer_id,
            'website_id' => $lead->website_id,
            'dealer_location_id' => $lead->dealer_location_id,
            'inquiry_type' => InquiryLead::INQUIRY_TYPES[0],
            'lead_types' => [$lead->lead_type],
            //'item_id' => $lead->inventory_id,
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
            'lead_source' => $status->source,
            'lead_status' => $status->status,
            'contact_type' => $status->task,
            'sales_person_id' => $status->sales_person_id,
            'cookie_session_id' => $tracking->session_id
        ];

        // Send Inquiry Params
        $sendInquiryParams = $sendRequestParams;
        $sendInquiryParams['inventory'] = [];//[$sendRequestParams['item_id']];

        // Get Inquiry Lead
        $inquiry = $this->prepareInquiryLead($website, $sendRequestParams);


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
            var_dump($inquiry->cookieSessionId);
            var_dump($lead->identifier);
        $this->trackingRepositoryMock
            ->shouldReceive('updateTrackLead')
            ->once()
            ->with($inquiry->cookieSessionId, $lead->identifier);

        // Mock Sales Person Repository
        $this->trackingUnitRepositoryMock
            ->shouldReceive('markUnitInquired')
            ->never();
            //->with($sendInquiryParams['cookie_session_id'], $inquiry->itemId, $inquiry->getUnitType());

        // Expects Auto Assign Job
        $this->expectsJobs(AutoAssignJob::class);

        // Expects Auto Responder Job
        $this->expectsJobs(AutoResponderJob::class);

        // Fake Mail
        //Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($sendRequestParams);

        // Assert a message was sent to the dealer...
        /*Mail::assertSent(InquiryEmail::class, function ($mail) use ($inquiry) {
            if(empty($inquiry->inquiryEmail)) {
                return false;
            }                
            return $mail->hasTo($inquiry->inquiryEmail);
        });*/

        // Match Lead Details
        $this->assertSame($result->identifier, $lead->identifier);
        $this->assertSame($result->full_name, $lead->full_name);
        $this->assertSame($result->email_address, $lead->email_address);
        $this->assertSame($result->phone_number, $lead->phone_number);

        // Assert tracking lead ID was set
        /*$this->assertDatabaseHas('website_tracking', [
            'session_id' => $tracking->session_id,
            'lead_id' => $result->identifier
        ]);*/

        // Assert inquired status was NOT set to tracking unit
        $this->assertDatabaseMissing('website_tracking_units', [
            'session_id' => $tracking->session_id,
            'inquired' => 1
        ]);
    }


    /**
     * Create an Even Number of Tracking Units
     * 
     * @param int $dealerId
     * @param string $sessionId
     * @return Collection<TrackingUnit>
     */
    private function createTrackingUnits(int $dealerId, string $sessionId): Collection {
        // Initialize Collection
        $units = [];
        $parts = [];
        $inventory = [];

        // Differentiate By Types
        $seeds = [
            ['type' => 'inventory', 'item' => 0],
            ['type' => 'part', 'item' => 0],
            ['type' => 'part', 'item' => 1],
            ['type' => 'inventory', 'item' => 1],
            ['type' => 'part', 'item' => 0],
            ['type' => 'inventory', 'item' => 1]
        ];

        // Loop Seeds
        collect($seeds)->each(function (array $seed) use(&$units, &$parts, &$inventory, $sessionId, $dealerId): void {
            // Create Inventory/Part
            if($seed['type'] === 'part') {
                if(!isset($parts[$seed['item']])) {
                    $parts[] = factory(Part::class)->create([
                        'dealer_id' => $dealerId
                    ]);
                }
                $itemId = $parts[$seed['item']]->id;
            } else {
                if(!isset($inventory[$seed['item']])) {
                    $inventory[] = factory(Inventory::class)->create([
                        'dealer_id' => $dealerId
                    ]);
                }
                $itemId = $inventory[$seed['item']]->inventory_id;
            }

            // Create Tracking Unit
            $units[] = factory(TrackingUnit::class)->create([
                'session_id' => $sessionId,
                'type' => $seed['type'],
                'inventory_id' => $itemId
            ]);
            sleep(1);
        });

        // Return Result
        return collect($units);
    }

    /**
     * Prepare Inquiry Lead
     * 
     * @param array $params
     * @return InquiryLead
     */
    private function prepareInquiryLead(Website $website, array $params) {
        // Set Website Domain
        $params['website_domain'] = $website->domain;

        // Get Inquiry From Details For Website
        $config = self::TEST_WEBSITE_CONFIG;
        $params['logo'] = $config['logo'];
        $params['logo_url'] = $config['logoUrl'];
        $params['from_name'] = $config['fromName'];

        // Get Inquiry Name/Email
        $details = $this->getInquiryDetails($params);

        // Get Data By Inquiry Type
        $vars = $this->getInquiryTypeVars($details);

        // Create Inquiry Lead
        return new InquiryLead($vars);
    }

    /**
     * Get Inquiry Name/Email Details
     * 
     * @param array $params
     * @return array_merge($params, array{'inquiry_email': string,
     *                                    'inquiry_name': string})
     */
    private function getInquiryDetails(array $params): array {
        // Get Inquiry Details From Dealer Location?
        if(!empty($params['dealer_location_id'])) {
            $dealerLocation = DealerLocation::find($params['dealer_location_id']);
            if(!empty($dealerLocation->name)) {
                $params['inquiry_name'] = $dealerLocation->name;
                $params['inquiry_email'] = $dealerLocation->email;
                return $params;
            }
        }

        // Get Inquiry Details From Inventory Item?
        if(!empty($params['item_id']) && !in_array($params['inquiry_type'], InquiryLead::NON_INVENTORY_TYPES)) {
            $inventory = Inventory::find($params['item_id']);
            if(!empty($inventory->dealerLocation->name)) {
                $params['inquiry_name'] = $inventory->dealerLocation->name;
                $params['inquiry_email'] = $inventory->dealerLocation->email;
                return $params;
            }
        }

        // Get Inquiry Details From Dealer
        $dealer = User::find($params['dealer_id']);
        $params['inquiry_name'] = $dealer->name;
        $params['inquiry_email'] = $dealer->email;
        return $params;
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
                $inventory = Inventory::find($params['item_id']);
                $params['stock'] = !empty($inventory->stock) ? $inventory->stock : '';
                $params['title'] = $inventory->title;
            break;
            case "part":
                $part = Part::find($params['item_id']);
                $params['stock'] = !empty($part->sku) ? $part->sku : '';
                $params['title'] = $part->title;
            break;
            case "showroom":
                $showroom = Showroom::find($params['item_id']);
                $title = $showroom->year . ' '. $showroom->manufacturer;
                $title .= (!empty($showroom->series) ? ' ' . $showroom->series : '');
                $params['title'] = $title . ' ' . $showroom->model;
            break;
        }

        // Return Updated Params Array
        return $params;
    }
}
