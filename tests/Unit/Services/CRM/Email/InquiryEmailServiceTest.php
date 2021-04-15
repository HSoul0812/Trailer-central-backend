<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Mail\InquiryEmail;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
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
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_GENERAL
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
            'lead_source' => self::TEST_SOURCE,
            'lead_status' => LeadStatus::STATUS_MEDIUM,
            'contact_type' => LeadStatus::TYPE_CONTACT
        ];

        // Get Inquiry Lead
        $inquiry = $this->prepareInquiryLead($sendRequestParams);


        /** @var InquiryEmailServiceInterface $service */
        $service = $this->app->make(InquiryEmailServiceInterface::class);

        // Fake Mail
        Mail::fake();


        // Validate Send Inquiry Result
        $result = $service->send($inquiry);

        // Assert a message was sent to the dealer...
        Mail::assertSent(InquiryEmail::class, function ($mail) use ($inquiry) {
            // Check Multiple Things for Successes!
            $successes = 0;

            // Inquiry Email Exists?
            if($inquiry->inquiryEmail && $mail->hasTo($inquiry->inquiryEmail)) {
                $successes++;
            }

            // BCC Exists?
            if($mail->hasBcc(InquiryLead::INQUIRY_BCC_TO[0])) {
                $successes++;
            }

            // Must Be 2!
            return ($successes === 2);
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
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_GENERAL
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
            'lead_source' => self::TEST_SOURCE,
            'lead_status' => LeadStatus::STATUS_MEDIUM,
            'contact_type' => LeadStatus::TYPE_CONTACT
        ];


        // Mock Website Config Repository
        $this->websiteConfigRepositoryMock
            ->shouldReceive('getValueOrDefault')
            ->once()
            ->with($websiteId, 'general/item_email_from')
            ->andReturn(self::TEST_WEBSITE_CONFIG);

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
