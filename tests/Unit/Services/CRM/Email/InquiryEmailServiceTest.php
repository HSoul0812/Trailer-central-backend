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
use App\Models\User\DealerLocation;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use App\Services\CRM\Leads\DTOs\InquiryLead;
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
class InquiryEmailServiceTest extends TestCase
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
     * @var LegacyMockInterface|WebsiteConfigRepositoryInterface
     */
    private $websiteConfigRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->websiteConfigRepositoryMock = Mockery::mock(WebsiteConfigRepositoryInterface::class);
        $this->app->instance(WebsiteConfigRepositoryInterface::class, $this->websiteConfigRepositoryMock);
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
        $websiteId = self::getTestWebsiteRandom();
        $website = Website::find($websiteId);

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_GENERAL
        ]);
        $status = factory(LeadStatus::class)->create([
            'tc_lead_identifier' => $lead->identifier
        ]);

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
            'lead_source' => $status->source,
            'lead_status' => $status->status,
            'contact_type' => $status->task
        ];

        // Get Inquiry Lead
        $inquiry = $this->prepareInquiryLead($website, $sendRequestParams);


        /** @var InquiryEmailServiceInterface $service */
        $service = $this->app->make(InquiryEmailServiceInterface::class);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($inquiry);

        // Assert a message was sent to the dealer...
        Mail::assertSent(InquiryEmail::class, function ($mail) use ($inquiry) {
            if(empty($inquiry->inquiryEmail)) {
                return false;
            }                
            return $mail->hasTo($inquiry->inquiryEmail);
        });

        // Result = true
        $this->assertTrue($result);
    }

    /**
     * @covers ::fill
     *
     * @throws BindingResolutionException
     */
    public function testFill()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_GENERAL
        ]);
        $status = factory(LeadStatus::class)->create([
            'tc_lead_identifier' => $lead->identifier
        ]);

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
            'lead_source' => $status->source,
            'lead_status' => $status->status,
            'contact_type' => $status->task
        ];


        /** @var InquiryEmailServiceInterface $service */
        $service = $this->app->make(InquiryEmailServiceInterface::class);

        // Validate Send Inquiry Result
        $result = $service->fill($sendRequestParams);

        // Result = true
        $this->assertSame($result->inquiryType, InquiryLead::INQUIRY_TYPES[0]);
        $this->assertSame($result->firstName, $lead->first_name);
        $this->assertSame($result->lastName, $lead->last_name);
        $this->assertSame($result->emailAddress, $lead->email_address);
        $this->assertSame($result->phoneNumber, $lead->phone_number);
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
