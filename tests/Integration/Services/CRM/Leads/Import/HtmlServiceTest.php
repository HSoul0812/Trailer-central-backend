<?php

declare(strict_types=1);

namespace Tests\Integration\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidImportFormatException;
use App\Exceptions\Tests\MissingTestDealerIdException;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\CRM\Leads\Import\HtmlService;
use App\Services\CRM\Leads\Import\HtmlServices\BoatsCom;
use App\Services\CRM\Leads\Import\HtmlServices\BoatTraderPortalAd;
use App\Services\CRM\Leads\Import\ImportSourceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\database\seeds\CRM\Leads\HtmlSeeder;
use Tests\Integration\WithMySqlConstraintViolationsParser;
use Tests\TestCase;

/**
 * Class HtmlServiceTest
 * @package Tests\Integration\Services\CRM\Leads\Import
 *
 * @coversDefaultClass \App\Services\CRM\Leads\Import\HtmlService
 */
class HtmlServiceTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    const INJECT_VARIABLES = [
        'LEAD_FIRST_NAME',
        'LEAD_LAST_NAME',
        'LEAD_PHONE_NUMBER',
        'LEAD_EMAIL_ADDRESS',
        'LEAD_NOTE',
        'INVENTORY_MODEL',
        'INVENTORY_BRAND',
        'INVENTORY_DESCRIPTION',
        'INVENTORY_YEAR',
        'INVENTORY_IDENTIFIER',
        'INVENTORY_STOCK',
        'LOCATION_NAME',
        'LOCATION_ADDRESS',
        'LOCATION_CITY',
        'LOCATION_COUNTY',
        'LOCATION_POSTALCODE',
    ];

    const EMAIL_WITH_AUTHOR = 'Author Email <email@trailercentral.com>';

    /**
     * @var HtmlSeeder
     */
    private $seeder;

    /**
     * Create the necessary data for the test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Given that I have a seeder
        $this->seeder = new HtmlSeeder();

        // And that I seeded the necessary data
        $this->seeder->seed();
    }

    /**
     * Test that when SUT receives a valid HTML
     * it confirms it finds a source
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     * @covers ::findSource
     * @dataProvider validEmailProvider
     *
     * @param $rawEmail
     * @return void
     *
     * @throws BindingResolutionException
     * @throws MissingTestDealerIdException
     */
    public function testFindSource($rawEmail)
    {
        // I inject the information into the raw email
        $email = $this->injectEmailInfo($rawEmail);

        // I generate the address to send to
        $toAddress = $this->seeder->dealer->getKey() . '@' . config('adf.imports.gmail.domain');

        // And I have a Parsed email
        $parsedEmail = $this->getParsedEmail(
            $this->seeder->lead->getKey(),
            $this->seeder->location->email,
            $email,
            $toAddress
        );

        // When I call findSource on the service
        $result = $this->getConcreteService()->findSource($parsedEmail);

        // I receive a valid source as a response
        $this->assertInstanceOf(ImportSourceInterface::class, $result);
    }

    /**
     * Test that when SUT is asked to get a lead
     * it returns a valid ADFLead
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     * @covers ::getLead
     * @dataProvider validEmailProvider
     *
     * @param $rawEmail
     * @param $source
     * @param bool $findSource
     * @return void
     *
     * @throws BindingResolutionException
     * @throws InvalidImportFormatException
     * @throws MissingTestDealerIdException
     */
    public function testGetLead($rawEmail, $source = null, bool $findSource = false): void
    {
        // I have a dealer
        $dealer = $this->seeder->dealer;

        // I inject the information into the raw email
        $email = $this->injectEmailInfo($rawEmail);

        // I generate the address to send to
        $toAddress = $this->seeder->dealer->getKey() . '@' . config('adf.imports.gmail.domain');

        // And I have a Parsed email
        $parsedEmail = $this->getParsedEmail(
            $this->seeder->lead->getKey(),
            $this->seeder->location->email,
            $email,
            $toAddress
        );

        // If classname received, make it
        if (gettype($source) == "string") {
            $source = app()->make($source);
        }

        // Should I find the source?
        if ($findSource) {
            $source = $this->getConcreteService()->findSource($parsedEmail);
        }

        // When I call getLead on the class with a valid and specific source
        $adfLead = $this->getConcreteService()->getLead($dealer, $parsedEmail, $source);

        // I get an ADFLead instance response
        $this->assertInstanceOf(ADFLead::class, $adfLead);

        // And the lead info is the same
        $lead = $this->seeder->lead;
        $this->assertSame($lead->first_name, $adfLead->getFirstName());
        $this->assertSame($lead->email_address, $adfLead->getEmail());
        $this->assertSame($lead->phone_number, $adfLead->getPhone());
        $this->assertSame($lead->dealer_id, $adfLead->getDealerId());
    }

    /**
     * Test that when SUT is asked to get a lead
     * it handles the errors correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     * @covers ::getLead
     * @dataProvider invalidEmailProvider
     *
     * @param $rawEmail
     * @param $toAddress
     * @return void
     *
     * @throws BindingResolutionException
     * @throws InvalidImportFormatException
     * @throws MissingTestDealerIdException
     */
    public function testInvalidGetLead($rawEmail, $toAddress): void
    {
        // I have a dealer
        $dealer = $this->seeder->dealer;

        // I inject the information into the raw email
        $email = $this->injectEmailInfo($rawEmail);

        // I generate the address to send to
        if (empty($toAddress)) {
            $toAddress = $this->seeder->dealer->getKey() . '@' . config('adf.imports.gmail.domain');
        }

        // And I have a Parsed email
        $parsedEmail = $this->getParsedEmail(
            $this->seeder->lead->getKey(),
            $this->seeder->location->email,
            $email,
            $toAddress
        );

        // I expect to get an exception
        $this->expectException(InvalidImportFormatException::class);

        // After I call getLead on the class
        $this->getConcreteService()->getLead($dealer, $parsedEmail);
    }

    /**
     * Test that SUT is properly bound by the application
     *
     * @group CRM
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForHtmlServiceInterfaceIsWorking(): void
    {
        $concreteService = $this->getConcreteService();

        self::assertInstanceOf(HtmlService::class, $concreteService);
    }

    /**
     * @return HtmlService
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     */
    protected function getConcreteService(): HtmlService
    {
        return $this->app->make(HtmlService::class);
    }

    /**
     * Get a parsed email
     *
     * @param int $id
     * @param string $from
     * @param string $body
     * @param null $to
     *
     * @return ParsedEmail
     * @throws MissingTestDealerIdException
     */
    private function getParsedEmail(int $id, string $from, string $body, $to = null): ParsedEmail
    {
        $parsedEmail = new ParsedEmail();
        $parsedEmail->setId((string) $id);

        if ($to === null) {
            $to = self::getTestDealerId() . '@' . config('adf.imports.gmail.domain');
        }

        $parsedEmail->setToEmail($to);
        $parsedEmail->setFrom($from);
        $parsedEmail->setSubject('Html Import');
        $parsedEmail->setBody($body);

        return $parsedEmail;
    }

    /**
     */
    public function validEmailProvider(): array
    {
        return [
            'Boats.com, given source' => [
                $this->getValidHtmlFromBoatsCom(),
                BoatsCom::class,
                false
            ],
            'Boats.com, find source' => [
                $this->getValidHtmlFromBoatsCom(),
                BoatsCom::class,
                true
            ],
            'Boats.com, no source' => [
                $this->getValidHtmlFromBoatsCom(),
                null,
                false
            ],
            'BoatTrader Portal, given source' => [
                $this->getValidHtmlFromBoatTraderPortal(),
                BoatTraderPortalAd::class,
                false
            ],
            'BoatTrader Portal, find source' => [
                $this->getValidHtmlFromBoatTraderPortal(),
                BoatTraderPortalAd::class,
                true
            ],
            'BoatTrader Portal, no source' => [
                $this->getValidHtmlFromBoatTraderPortal(),
                null,
                false
            ],
        ];
    }

    /**
     */
    public function invalidEmailProvider(): array
    {
        return [
            'Boats.com, given source' => [
                $this->getInvalidHtmlFromBoatsCom(),
                self::EMAIL_WITH_AUTHOR
            ],
            'BoatTrader Portal, given source' => [
                $this->getInvalidHtmlFromBoatTraderPortal(),
                self::EMAIL_WITH_AUTHOR
            ],
            'Boats.com, email with author' => [
                $this->getInvalidHtmlFromBoatsCom(),
                self::EMAIL_WITH_AUTHOR
            ],
            'Boats.com, invalid email' => [
                $this->getInvalidHtmlFromBoatsCom(),
                ' '
            ],
        ];
    }

    /**
     * Raw HTML for a boats.com email
     *
     * @return string
     */
    private function getValidHtmlFromBoatsCom(): string
    {
        return "INDIVIDUAL PROSPECT:
                Name:                   {{LEAD_FIRST_NAME}} {{LEAD_LAST_NAME}}
                Telephone:              {{LEAD_PHONE_NUMBER}}
                Email:                  {{LEAD_EMAIL_ADDRESS}}

                LEAD INFORMATION:
                Lead date:              October 10, 2022 3:58 PM PDT
                Lead source:            Boats.com
                Lead status:            Lead
                Lead request type:      MORE-INFO-REQUEST

                SALES BOAT:
                Sale class:             New
                Make:                   {{INVENTORY_BRAND}}
                Model description:      {{INVENTORY_DESCRIPTION}}
                Year:                   {{INVENTORY_YEAR}}
                HIN:                    {{INVENTORY_IDENTIFIER}}
                Stock Number:           {{INVENTORY_STOCK}}
                URI:                    http://www.boats.com/power-boats/2023-crownline-260-xss-8218900/

                OFFICEINFO:
                Sales Contact:          Kellie rhoderiver
                Name:                   {{LOCATION_NAME}}
                Address:                {{LOCATION_ADDRESS}}
                Address 2:
                City:                   {{LOCATION_CITY}}
                State/Province:         {{LOCATION_COUNTY}}
                Zip/Postal code:        {{LOCATION_POSTALCODE}}
                Country:                US";
    }

    /**
     * Raw HTML for a Boat Trader Portal email
     *
     * @return string
     */
    private function getValidHtmlFromBoatTraderPortal(): string
    {
        return "NEW SALES LEAD:
                FROM :                  BoatTrader PORTAL AD
                Name:                   {{LEAD_FIRST_NAME}} {{LEAD_LAST_NAME}}
                Telephone:              {{LEAD_PHONE_NUMBER}}
                Email:                  {{LEAD_EMAIL_ADDRESS}}
                Customer Comments:      {{LEAD_NOTE}}

                ADDITIONAL LEAD DETAILS:
                Lead date:              October 10, 2022 3:52 PM PDT
                Lead source:            BoatTrader PORTAL AD
                Website link:           https://www.boattrader.com/boat/2023-crownline-260-xss-8218900/
                Lead request type:      INTERESTED-IN

                PROSPECT DETAILS:
                Name:                   {{LEAD_FIRST_NAME}} {{LEAD_LAST_NAME}}
                Telephone:              {{LEAD_PHONE_NUMBER}}
                Email:                  {{LEAD_EMAIL_ADDRESS}}

                SALES BOAT:
                Sale class:             New
                Make:                   {{INVENTORY_BRAND}}
                Model description:      {{INVENTORY_MODEL}}
                Year:                   {{INVENTORY_YEAR}}
                HIN:                    {{INVENTORY_IDENTIFIER}}

                LEAD DESTINATION:
                Address:                {{LOCATION_ADDRESS}}
                Email:                  {{LEAD_FIRST_NAME}} {{LEAD_LAST_NAME}} <{{LEAD_EMAIL_ADDRESS}}>";
    }

    /**
     * Raw HTML for a boats.com email
     *
     * @return string
     */
    private function getInvalidHtmlFromBoatsCom(): string
    {
        return "
                SALES BOAT:
                Sale class:             New
                Make:                   {{INVENTORY_BRAND}}
                Model description:      {{INVENTORY_DESCRIPTION}}
                Year:                   {{INVENTORY_YEAR}}
                HIN:                    {{INVENTORY_IDENTIFIER}}
                Stock Number:           {{INVENTORY_STOCK}}
                URI:                    http://www.boats.com/power-boats/2023-crownline-260-xss-8218900/

                OFFICEINFO:
                Sales Contact:          Kellie rhoderiver
                Name:                   {{LOCATION_NAME}}
                Address:                {{LOCATION_ADDRESS}}
                Address 2:
                City:                   {{LOCATION_CITY}}
                State/Province:         {{LOCATION_COUNTY}}
                Zip/Postal code:        {{LOCATION_POSTALCODE}}
                Country:                US";
    }

    /**
     * Raw HTML for a Boat Trader Portal email
     *
     * @return string
     */
    private function getInvalidHtmlFromBoatTraderPortal(): string
    {
        return "
                Website link:           https://www.boattrader.com/boat/2023-crownline-260-xss-8218900/
                Lead request type:      INTERESTED-IN

                PROSPECT DETAILS:
                Name:                   {{LEAD_FIRST_NAME}} {{LEAD_LAST_NAME}}
                Telephone:              {{LEAD_PHONE_NUMBER}}
                Email:                  {{LEAD_EMAIL_ADDRESS}}

                SALES BOAT:
                Sale class:             New
                Make:                   {{INVENTORY_BRAND}}
                Model description:      {{INVENTORY_MODEL}}
                Year:                   {{INVENTORY_YEAR}}
                HIN:                    {{INVENTORY_IDENTIFIER}}

                LEAD DESTINATION:
                Address:                {{LOCATION_ADDRESS}}
                Email:                  {{LEAD_FIRST_NAME}} {{LEAD_LAST_NAME}} <{{LEAD_EMAIL_ADDRESS}}>";
    }

    /**
     * Clean up the database after the test is done
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * Replace variables in the following format:
     * MODEL_PROPERTY_VALUE
     * where the first word is the model, and anything
     * after that is a property, in lowercase
     *
     * @param $rawEmail
     * @return array|string|string[]
     */
    private function injectEmailInfo($rawEmail)
    {
        $injectedEmail = $rawEmail;

        foreach (self::INJECT_VARIABLES as $variable) {
            $parts = explode('_', $variable);
            $model = strtolower($parts[0]);
            unset($parts[0]);
            $property = strtolower(implode('_', $parts));
            $injectedEmail = str_replace(
                '{{' . $variable .  '}}',
                $this->seeder->$model->$property,
                $injectedEmail
            );
        }

        return $injectedEmail;
    }
}
