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
            if($mail->hasBcc(InquiryLead::INQUIRY_BCC_TO[0]['email'])) {
                $successes++;
            }

            // Must Be 2!
            return ($successes === 2);
        });

        // Result = true
        $this->assertTrue($result);
    }

    /**
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSendDev()
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
            'metadata' => $this->getMetadata(true),
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
            if($mail->hasTo(InquiryLead::INQUIRY_DEV_TO[0]['email'])) {
                $successes++;
            }

            // BCC Does NOT Exist?
            var_dump($mail->bcc);
            die;
            if($this->assertTrue(empty($mail->bcc[0]['address']))) {
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
     * Get Fully Constructed Metadata
     * 
     * @param bool $isDev
     */
    private function getMetadata(bool $isDev = false) {
        // Get Generic Metadata
        $metadata = [
            'contact-address' => ["ben@trailercentral.com"],
            'adf-contact-address' => false,
            'subject' => 'Inventory Information Request on test-account-site.trailercentral.com',
            'domain' => 'test-account-site.trailercentral.com',
            'POST_DATA' => [
                'referral' => '/2019-winnebago-horizon-40a-motorhome-uEwh.html',
                'inquiry_t' => '',
                'inquiry_v' => '',
                'business_name' => 'Stevbrussy',
                'first_name' => 'StevbrussyAM',
                'last_name' => 'StevbrussyAM',
                'preferred_location' => '14427',
                'preferred_contact' => 'email',
                'email' => 'stevKisa@insite.pw',
                'phone' => '81348638999',
                'zip' => '134455',
                'comments' => 'The Most Inexpensive Cialis Propecia Online Pharmacy New York',
            ],
            'COOKIE_DATA' => [
                'firstvisit' => '2019-07-30T03:10:18-04:00',
                'PHPSESSID' => 'm02fgvetueteb3js4357hp523d',
                'page-views' => '1'
            ],
            'SERVER_DATA' => [
                'USER' => 'www-data',
                'HOME' => '/var/www',
                'HTTP_COOKIE' => 'firstvisit=2019-07-30T03%3A10%3A18-04%3A00; PHPSESSID=m02fgvetueteb3js4357hp523d; page-views=1',
                'HTTP_PRAGMA' => 'no-cache',
                'HTTP_CONTENT_TYPE' => 'application\/x-www-form-urlencoded',
                'HTTP_REFERER' => 'http://test-account-site.trailercentral.com/2019-winnebago-horizon-40a-motorhome-uEwh.html',
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.79 Safari/537.36',
                'HTTP_ACCEPT' => '*/*',
                'HTTP_CONTENT_LENGTH' => '903',
                'HTTP_X_AMZN_TRACE_ID' => 'Root=1-5d3fed5a-9edcad462eecad2a531357d8',
                'HTTP_HOST' => 'test-account-site.trailercentral.com',
                'HTTP_X_FORWARDED_PORT' => '80',
                'HTTP_X_FORWARDED_PROTO' => 'http',
                'HTTP_X_FORWARDED_FOR' => '31.184.238.17',
                'REDIRECT_STATUS' => '200',
                'SERVER_NAME' => 'localhost',
                'SERVER_PORT' => '80',
                'SERVER_ADDR' => '10.0.0.146',
                'REMOTE_PORT' => '',
                'REMOTE_ADDR' => '31.184.238.17',
                'SERVER_SOFTWARE' => 'nginx/1.14.1',
                'GATEWAY_INTERFACE' => 'CGI/1.1',
                'REQUEST_SCHEME' => 'http',
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'DOCUMENT_ROOT' => '/var/www/html',
                'DOCUMENT_URI' => '/index.php',
                'REQUEST_URI' => '/inventory-post',
                'SCRIPT_NAME' => '/index.php',
                'CONTENT_LENGTH' => '903',
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                'REQUEST_METHOD' => 'POST',
                'QUERY_STRING' => '',
                'TC_ROOT' => '/var/www/html',
                'SCRIPT_FILENAME' => '/var/www/html/index.php',
                'FCGI_ROLE' => 'RESPONDER',
                'PHP_SELF' => '/index.php',
                'REQUEST_TIME_FLOAT' => 1564470618.648725,
                'REQUEST_TIME' => 1564470618
            ],
            'SPAM_SCORE' => 5,
            'SPAM_FAILURES' => [
                'COOKIE_X_HAS_JAVASCRIPT_EMPTY',
                'POST_X_HAS_JAVASCRIPT_EMPTY',
                'BUSINESS_NAME_OVERFILL',
                'POST_X_PAGE_LOADED_EMPTY',
                'MESSAGE_MATCH_KNOWN[=1]'
            ],
            "REAL_TO" => [
                'josh+spam-notify@trailercentral.com'
            ]
        ];

        // Is Dev?!
        if($isDev) {
            $metadata['IS_DEV'] = 1;
        }

        // Return Result
        return json_encode($metadata);
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
