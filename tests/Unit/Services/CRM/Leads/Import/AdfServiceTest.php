<?php

namespace Tests\Unit\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidImportFormatException;
use App\Helpers\SanitizeHelper;
use App\Models\Inventory\Inventory;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Models\Website\Website;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\CRM\Leads\Import\ADFService;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Mockery\LegacyMockInterface;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

/**
 * Test for App\Services\CRM\Leads\Import\ADFService
 *
 * Class AdfServiceTest
 * @package Tests\Unit\Services\CRM\Leads\Import
 *
 * @coversDefaultClass \App\Services\CRM\Leads\Import\ADFService
 */
class AdfServiceTest extends TestCase
{
    private const LEAD_DATE_SUBMITTED = '11.11.2011';
    private const INVENTORY_YEAR = '2020';
    private const INVENTORY_MAKE = 'inventory_make';
    private const INVENTORY_MODEL = 'inventory_model';
    private const INVENTORY_STOCK = 'inventory_stock';
    private const INVENTORY_VIN = 'inventory_vin';
    private const LEAD_FIRST_NAME = 'lead_first_name';
    private const LEAD_LAST_NAME = 'lead_last_name';
    private const LEAD_EMAIL = 'lead@email.com';
    private const LEAD_PHONE_NUMBER = 123456;
    private const LEAD_COMMENTS = 'some_comments';
    private const LEAD_ADDRESS = 'lead_address';
    private const LEAD_CITY = 'lead_city';
    private const LEAD_STATE = 'lead_state';
    private const LEAD_ZIP = 'lead_zip';
    private const DEALER_ID = PHP_INT_MAX;
    private const WEBSITE_ID = PHP_INT_MAX - 2;
    private const DEALER_LOCATION_ID = PHP_INT_MAX - 1;
    private const DEALER_NAME = 'dealer_name';
    private const LOCATION_CONTACT = 'location_contact';
    private const LOCATION_DOMAIN = 'location_domain';
    private const LOCATION_EMAIL = 'location_email';
    private const LOCATION_PHONE = 'location_phone';
    private const LOCATION_ADDRESS = 'location_address';
    private const LOCATION_CITY = 'location_city';
    private const LOCATION_REGION = 'location_region';
    private const LOCATION_POSTAL_CODE = 'location_postal_code';
    private const LOCATION_COUNTRY = 'location_country';

    private const VALID_ADF = '<?xml version="1.0" encoding="UTF-8"?><?adf version="1.0"?>
        <adf>
         <prospect>
          <requestdate>%LEAD_DATE_SUBMITTED%</requestdate>
          <vehicle>
           <year>%INVENTORY_YEAR%</year>
           <make>%INVENTORY_MAKE%</make>
           <model>%INVENTORY_MODEL%</model>
           <stock>%INVENTORY_STOCK%</stock>
           <vin>%INVENTORY_VIN%</vin>
          </vehicle>
          <customer>
           <contact>
            <name part="first">%LEAD_FIRST_NAME%</name>
            <name part="last">%LEAD_LAST_NAME%</name>
            <email>%LEAD_EMAIL%</email>
            <phone>%LEAD_PHONE_NUMBER%</phone>
           </contact>
           <comments><![CDATA[%LEAD_COMMENTS%]]></comments>
           <address type="home">
            <street>%LEAD_ADDRESS%</street>
            <city>%LEAD_CITY%</city>
            <regioncode>%LEAD_STATE%</regioncode>
            <postalcode>%LEAD_ZIP%</postalcode>
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

    private const NOT_VALID_ADF = 'not_valid_adf';

    /**
     * @var DealerLocationRepositoryInterface|LegacyMockInterface
     */
    protected $locationRepository;

    /**
     * @var InventoryRepositoryInterface|LegacyMockInterface
     */
    protected $inventoryRepository;

    /**
     * @var SanitizeHelper|LegacyMockInterface
     */
    protected $sanitizeHelper;

    /**
     * @var LoggerInterface|LegacyMockInterface
     */
    protected $logMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->instanceMock('locationRepository', DealerLocationRepositoryInterface::class);
        $this->instanceMock('inventoryRepository', InventoryRepositoryInterface::class);
        $this->instanceMock('sanitizeHelper', SanitizeHelper::class);
        $this->instanceMock('logMock', LoggerInterface::class);
    }

    /**
     * @group CRM
     * @covers ::isSatisfiedBy
     *
     * @dataProvider validAdfParamsProvider
     */
    public function testTrueIsSatisfiedBy($dealer, $email)
    {
        /** @var ADFService $service */
        $service = $this->app->make(ADFService::class);
        $result = $service->isSatisfiedBy($email);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::isSatisfiedBy
     *
     * @dataProvider notValidAdfParamsProvider
     */
    public function testFalseIsSatisfiedBy($dealer, $email)
    {
        /** @var ADFService $service */
        $service = $this->app->make(ADFService::class);
        $result = $service->isSatisfiedBy($email);

        $this->assertFalse($result);
    }

    /**
     * @group CRM
     * @covers ::getLead
     *
     * @dataProvider validAdfParamsProvider
     */
    public function testGetLead($dealer, $email)
    {
        /** @var DealerLocation $location */
        $location = $this->getEloquentMock(DealerLocation::class);
        $location->dealer_location_id = PHP_INT_MAX - 123456;
        $locations = new Collection([$location]);

        /** @var Inventory $inventoryItem */
        $inventoryItem = $this->getEloquentMock(Inventory::class);
        $inventoryItem->inventory_id = PHP_INT_MAX - 654321;
        $inventory = new Collection([$inventoryItem]);

        $this->locationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($locations);

        $this->inventoryRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($inventory);

        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock
            ->shouldReceive('info')
            ->once();

        /** @var ADFService $service */
        $service = $this->app->make(ADFService::class);
        $result = $service->getLead($dealer, $email);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(ADFLead::class, $result);

        $this->assertEquals(self::DEALER_ID, $result->getDealerId());
        $this->assertEquals(self::WEBSITE_ID, $result->getWebsiteId());
        $this->assertEquals($location->dealer_location_id, $result->getLocationId());
        $this->assertEquals($inventoryItem->inventory_id, $result->getVehicleId());
        $this->assertEquals(self::LEAD_FIRST_NAME, $result->getFirstName());
        $this->assertEquals(self::LEAD_LAST_NAME, $result->getLastName());
        $this->assertEquals(self::LEAD_PHONE_NUMBER, $result->getPhone());
        $this->assertEquals(self::LEAD_EMAIL, $result->getEmail());
        $this->assertEquals(self::LEAD_ADDRESS, $result->getAddrStreet());
        $this->assertEquals(self::LEAD_CITY, $result->getAddrCity());
        $this->assertEquals(self::LEAD_STATE, $result->getAddrState());
        $this->assertEquals(self::LEAD_ZIP, $result->getAddrZip());
        $this->assertEquals(self::LEAD_COMMENTS, $result->getComments());
        $this->assertEquals(self::INVENTORY_YEAR, $result->getVehicleYear());
        $this->assertEquals(self::INVENTORY_MAKE, $result->getVehicleMake());
        $this->assertEquals(self::INVENTORY_MODEL, $result->getVehicleModel());
        $this->assertEquals(self::INVENTORY_STOCK, $result->getVehicleStock());
        $this->assertEquals(self::INVENTORY_VIN, $result->getVehicleVin());
    }

    /**
     * @group CRM
     * @covers ::getLead
     *
     * @dataProvider validAdfParamsProvider
     */
    public function testGetLeadWithoutInventoryAndLocation($dealer, $email)
    {
        $locations = new Collection([]);
        $inventory = new Collection([]);

        $this->locationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($locations);

        $this->inventoryRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($inventory);

        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock
            ->shouldReceive('info')
            ->once();

        /** @var ADFService $service */
        $service = $this->app->make(ADFService::class);
        $result = $service->getLead($dealer, $email);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(ADFLead::class, $result);

        $this->assertEquals(self::DEALER_ID, $result->getDealerId());
        $this->assertEquals(self::WEBSITE_ID, $result->getWebsiteId());
        $this->assertEmpty($result->getLocationId());
        $this->assertEmpty($result->getVehicleId());
        $this->assertEquals(self::LEAD_FIRST_NAME, $result->getFirstName());
        $this->assertEquals(self::LEAD_LAST_NAME, $result->getLastName());
        $this->assertEquals(self::LEAD_PHONE_NUMBER, $result->getPhone());
        $this->assertEquals(self::LEAD_EMAIL, $result->getEmail());
        $this->assertEquals(self::LEAD_ADDRESS, $result->getAddrStreet());
        $this->assertEquals(self::LEAD_CITY, $result->getAddrCity());
        $this->assertEquals(self::LEAD_STATE, $result->getAddrState());
        $this->assertEquals(self::LEAD_ZIP, $result->getAddrZip());
        $this->assertEquals(self::LEAD_COMMENTS, $result->getComments());
        $this->assertEquals(self::INVENTORY_YEAR, $result->getVehicleYear());
        $this->assertEquals(self::INVENTORY_MAKE, $result->getVehicleMake());
        $this->assertEquals(self::INVENTORY_MODEL, $result->getVehicleModel());
        $this->assertEquals(self::INVENTORY_STOCK, $result->getVehicleStock());
        $this->assertEquals(self::INVENTORY_VIN, $result->getVehicleVin());
    }

    /**
     * @group CRM
     * @covers ::getLead
     *
     * @dataProvider notValidAdfParamsProvider
     */
    public function testGetLeadWithNotValidAdf($dealer, $email)
    {
        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock
            ->shouldReceive('error')
            ->once();

        $this->expectException(InvalidImportFormatException::class);

        /** @var ADFService $service */
        $service = $this->app->make(ADFService::class);
        $service->getLead($dealer, $email);
    }

    /**
     * @return array
     */
    public function validAdfParamsProvider(): array
    {
        /** @var User $dealer */
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = self::DEALER_ID;

        /** @var Website $website */
        $website = $this->getEloquentMock(Website::class);
        $website->id = self::WEBSITE_ID;

        $this->initHasOneRelation($dealer, 'website', $website);

        $adf = self::VALID_ADF;

        $selfReflection = new \ReflectionClass(self::class);

        foreach ($selfReflection->getConstants() as $name => $value) {
            $adf = str_replace("%{$name}%", $value, $adf);
        }

        $email = new ParsedEmail();
        $email->setBody($adf);

        return [[$dealer, $email]];
    }

    /**
     * @return array
     */
    public function notValidAdfParamsProvider(): array
    {
        $dealer = $this->getEloquentMock(User::class);

        $email = new ParsedEmail();
        $email->setBody(self::NOT_VALID_ADF);

        return [[$dealer, $email]];
    }
}
