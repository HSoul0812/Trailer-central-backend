<?php

namespace Tests\Integration\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidImportFormatException;
use App\Exceptions\Tests\MissingTestDealerIdException;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\CRM\Leads\Import\ADFService;
use App\Services\CRM\Leads\Import\ImportSourceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\database\seeds\CRM\Leads\ADFSeeder;
use Tests\Integration\WithMySqlConstraintViolationsParser;
use Tests\TestCase;

/**
 * Class HtmlServiceTest
 * @package Tests\Integration\Services\CRM\Leads\Import
 *
 * @coversDefaultClass \App\Services\CRM\Leads\Import\ADFService
 */
class ADFServiceTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    const INJECT_VARIABLES = [
        'LEAD_FIRST_NAME',
        'LEAD_LAST_NAME',
        'LEAD_PHONE_NUMBER',
        'LEAD_EMAIL_ADDRESS',
        'LEAD_NOTE',
        'LEAD_CREATED_AT',
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

    public const VALID_ADF = '<?xml version="1.0" encoding="UTF-8"?><?adf version="1.0"?>
        <adf>
         <prospect>
          <requestdate>%LEAD_CREATED_AT%</requestdate>
          <vehicle>
           <year>%INVENTORY_YEAR%</year>
           <make>%INVENTORY_MAKE%</make>
           <model>%INVENTORY_MODEL%</model>
           <stock>%INVENTORY_STOCK%</stock>
           <vin>%INVENTORY_IDENTIFIER%</vin>
          </vehicle>
          <customer>
           <contact>
            <name part="first">%LEAD_FIRST_NAME%</name>
            <name part="last">%LEAD_LAST_NAME%</name>
            <email>%LEAD_EMAIL_ADDRESS%</email>
            <phone>%LEAD_PHONE_NUMBER%</phone>
           </contact>
           <comments><![CDATA[%LEAD_COMMENTS%]]></comments>
           <address type="home">
            <street>%LOCATION_ADDRESS%</street>
            <city>%LOCATION_CITY%</city>
            <regioncode>%LOCATION_COUNTY%</regioncode>
            <postalcode>%LOCATION_POSTALCODE%</postalcode>
           </address>
          </customer>
          <vendor>
           <id sequence="1" source="DealerID">%DEALER_ID%</id>
           <id sequence="2" source="DealerLocationID">%DEALER_LOCATION_ID%</id>
           <vendorname>%DEALER_NAME%</vendorname>
           <contact>
            <name part="full">%LOCATION_CONTACT%</name>
            <url>%LOCATION_DOMAIN%</url>
            <email>%LOCATION_EMAIL%</email>
            <phone>%LOCATION_PHONE%</phone>
            <address type="work">
             <street>%LOCATION_ADDRESS%</street>
             <city>%LOCATION_CITY%</city>
             <regioncode>%LOCATION_REGION%</regioncode>
             <postalcode>%LOCATION_POSTAL_CODE%</postalcode>
             <country>%LOCATION_COUNTRY%</country>
            </address>
           </contact>
           <provider>
            <name part="full">TrailerCentral</name>
           </provider>
          </vendor>
         </prospect>
        </adf>';

    /**
     * @var ADFSeeder
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
        $this->seeder = new ADFSeeder();

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
     *
     * @return void
     *
     * @throws BindingResolutionException
     * @throws MissingTestDealerIdException
     */
    public function testFindSource()
    {
        // I inject the information into the raw email
        $email = $this->injectEmailInfo(self::VALID_ADF);

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
     * @return void
     *
     * @throws BindingResolutionException
     * @throws InvalidImportFormatException
     * @throws MissingTestDealerIdException
     */
    public function testGetLead(): void
    {
        // I have a dealer
        $dealer = $this->seeder->dealer;

        // I inject the information into the raw email
        $email = $this->injectEmailInfo(self::VALID_ADF);

        // I generate the address to send to
        $toAddress = $this->seeder->dealer->getKey() . '@' . config('adf.imports.gmail.domain');

        // And I have a Parsed email
        $parsedEmail = $this->getParsedEmail(
            $this->seeder->lead->getKey(),
            $this->seeder->location->email,
            $email,
            $toAddress
        );

        // When I call getLead on the class with a valid and specific source
        $adfLead = $this->getConcreteService()->getLead($dealer, $parsedEmail);

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
     * it returns a valid ADFLead
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     * @covers ::getLead
     * @return void
     *
     * @throws BindingResolutionException
     * @throws InvalidImportFormatException
     * @throws MissingTestDealerIdException
     */
    public function testGetLeadWithInvalidEmails(): void
    {
        // I have a dealer
        $dealer = $this->seeder->dealer;

        // I inject the information into the raw email
        $email = $this->injectEmailInfo(self::VALID_ADF);

        // I generate the address to send to
        $toAddress = $this->seeder->dealer->getKey() . '@' . config('adf.imports.gmail.domain');

        // And I have a Parsed email
        $parsedEmail = $this->getParsedEmail(
            $this->seeder->lead->getKey(),
            $this->seeder->location->email,
            $email,
            $toAddress
        );

        // When I call getLead on the class with a valid and specific source
        $adfLead = $this->getConcreteService()->getLead($dealer, $parsedEmail);

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
     * Test that SUT is properly bound by the application
     *
     * @group CRM
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForADFServiceInterfaceIsWorking(): void
    {
        $concreteService = $this->getConcreteService();

        self::assertInstanceOf(ADFService::class, $concreteService);
    }

    /**
     * @return ADFService
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     */
    protected function getConcreteService(): ADFService
    {
        return $this->app->make(ADFService::class);
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
        $parsedEmail->setSubject('ADF Import');
        $parsedEmail->setBody($body);

        return $parsedEmail;
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
                '%' . $variable .  '%',
                $this->seeder->$model->$property,
                $injectedEmail
            );
        }

        return $injectedEmail;
    }
}
