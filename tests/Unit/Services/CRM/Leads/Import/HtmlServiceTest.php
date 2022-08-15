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
use App\Services\CRM\Leads\Import\HtmlService;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * Test for App\Services\CRM\Leads\Import\HtmlService
 *
 * Class HtmlServiceTest
 * @package Tests\Unit\Services\CRM\Leads\Import
 *
 * @coversDefaultClass \App\Services\CRM\Leads\Import\HtmlService
 */
class HtmlServiceTest extends TestCase
{
    private const DEALER_ID = PHP_INT_MAX;
    private const WEBSITE_ID = PHP_INT_MAX - 1;

    private const LEAD_ADDRESS = 'some_address';
    private const LEAD_FIRST_NAME = 'lead_first_name';
    private const LEAD_LAST_NAME = 'lead_last_name';
    private const LEAD_TELEPHONE = 123456;
    private const LEAD_EMAIL = 'lead@email.com';
    private const LEAD_DATE = 'January 15, 2022 8:51 PM UTC';
    private const LEAD_SOURCE = 'BoatTrader PORTAL AD';
    private const LEAD_REQUEST_TYPE = 'INTERESTED-IN';
    private const LEAD_ITEM_SALES_CLASS = 'Used';
    private const LEAD_ITEM_MAKE = 'item_make';
    private const LEAD_ITEM_MODEL_DESCRIPTION = 'item_model_description';
    private const LEAD_ITEM_YEAR = 2020;
    private const LEAD_ITEM_IMT_ID = 654321;
    private const LEAD_ITEM_URI = 'some_uri';
    private const OFFICE_INFO_NAME = 'office_info_name';
    private const OFFICE_INFO_CITY = 'office_info_city';
    private const OFFICE_INFO_STATE = 'office_info_state';
    private const OFFICE_INFO_ZIP = 'office_info_zip';
    private const CUSTOMER_COMMENTS = 'customer_comments';

    private const VALID_HTML = "<p>LEAD DESTINATION:</p><p>Address:%LEAD_ADDRESS%</p><br/><p>INDIVIDUAL PROSPECT:</p>
    <p>Name:                   %LEAD_FIRST_NAME% %LEAD_LAST_NAME%</p><p>Telephone:%LEAD_TELEPHONE%</p><p>Email:%LEAD_EMAIL%</p><br/>
    <p> LEAD INFORMATION: </p><p>Lead date:%LEAD_DATE%</p><p>Lead source:%LEAD_SOURCE%</p><p>Lead request type:      %LEAD_REQUEST_TYPE%</p><br/>
    <p>SALES BOAT:</p><p>Sale class:%LEAD_ITEM_SALES_CLASS%</p><p>Make:%LEAD_ITEM_MAKE%</p><p>Model description:%LEAD_ITEM_MODEL_DESCRIPTION%</p>
    <p>Year:%LEAD_ITEM_YEAR%</p><p>IMT ID:%LEAD_ITEM_IMT_ID%</p><p>URI:%LEAD_ITEM_URI%</p><br/><p>OFFICEINFO:</p><p>Name:%OFFICE_INFO_NAME%</p>
    <p>City:%OFFICE_INFO_CITY%</p><p>State/Province:%OFFICE_INFO_STATE%</p><p>Zip/Postal code:%OFFICE_INFO_ZIP%</p><p>CUSTOMER COMMENTS:%CUSTOMER_COMMENTS%</p>";

    private const NOT_VALID_HTML = "<p>LEAD DESTINATION:</p><p>Address:%LEAD_ADDRESS%</p><br/><p>INDIVIDUAL PROSPECT:</p>
    <p>Name:                   %LEAD_FIRST_NAME% %LEAD_LAST_NAME%</p><p>Telephone:%LEAD_TELEPHONE%</p><p>Email:%LEAD_EMAIL%</p><br/>
    <p> LEAD INFORMATION: </p><p>Lead date:%LEAD_DATE%</p><p>Lead source:%LEAD_SOURCE%</p><p>Lead request type:      %LEAD_REQUEST_TYPE%</p><br/>";

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

    public function setUp(): void
    {
        parent::setUp();

        $this->instanceMock('locationRepository', DealerLocationRepositoryInterface::class);
        $this->instanceMock('inventoryRepository', InventoryRepositoryInterface::class);
        $this->instanceMock('sanitizeHelper', SanitizeHelper::class);
    }

    /**
     * @group CRM
     * @covers ::isSatisfiedBy
     *
     * @dataProvider validHtmlParamsProvider
     */
    public function testTrueIsSatisfiedBy($dealer, $email)
    {
        /** @var HtmlService $service */
        $service = $this->app->make(HtmlService::class);
        $result = $service->isSatisfiedBy($email);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::isSatisfiedBy
     *
     * @dataProvider notValidHtmlParamsProvider
     */
    public function testFalseIsSatisfiedBy($dealer, $email)
    {
        /** @var HtmlService $service */
        $service = $this->app->make(HtmlService::class);
        $result = $service->isSatisfiedBy($email);

        $this->assertFalse($result);
    }

    /**
     * @group CRM
     * @covers ::getLead
     *
     * @dataProvider validHtmlParamsProvider
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

        $this->sanitizeHelper
            ->shouldReceive('removeBrokenCharacters')
            ->with(self::CUSTOMER_COMMENTS)
            ->once()
            ->andReturn(self::CUSTOMER_COMMENTS);

        $this->locationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($locations);

        $this->inventoryRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($inventory);

        Log::shouldReceive('info');

        /** @var HtmlService $service */
        $service = $this->app->make(HtmlService::class);
        $result = $service->getLead($dealer, $email);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(ADFLead::class, $result);

        $this->assertEquals(self::DEALER_ID, $result->getDealerId());
        $this->assertEquals(self::WEBSITE_ID, $result->getWebsiteId());
        $this->assertEquals($location->dealer_location_id, $result->getLocationId());
        $this->assertEquals($inventoryItem->inventory_id, $result->getVehicleId());
        $this->assertEquals(self::LEAD_FIRST_NAME, $result->getFirstName());
        $this->assertEquals(self::LEAD_LAST_NAME, $result->getLastName());
        $this->assertEquals(self::LEAD_TELEPHONE, $result->getPhone());
        $this->assertEquals(self::LEAD_EMAIL, $result->getEmail());
        $this->assertEquals(self::OFFICE_INFO_NAME, $result->getAddrStreet());
        $this->assertEquals(self::OFFICE_INFO_CITY, $result->getAddrCity());
        $this->assertEquals(self::OFFICE_INFO_STATE, $result->getAddrState());
        $this->assertEquals(self::OFFICE_INFO_ZIP, $result->getAddrZip());
        $this->assertEquals(self::CUSTOMER_COMMENTS, $result->getComments());
    }

    /**
     * @group CRM
     * @covers ::getLead
     *
     * @dataProvider validHtmlParamsProvider
     */
    public function testGetLeadWithoutLocationAndInventory($dealer, $email)
    {
        $locations = new Collection([]);
        $inventory = new Collection([]);

        $this->sanitizeHelper
            ->shouldReceive('removeBrokenCharacters')
            ->with(self::CUSTOMER_COMMENTS)
            ->once()
            ->andReturn(self::CUSTOMER_COMMENTS);

        $this->locationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($locations);

        $this->inventoryRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($inventory);

        Log::shouldReceive('info');

        /** @var HtmlService $service */
        $service = $this->app->make(HtmlService::class);
        $result = $service->getLead($dealer, $email);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(ADFLead::class, $result);

        $this->assertEquals(self::DEALER_ID, $result->getDealerId());
        $this->assertEquals(self::WEBSITE_ID, $result->getWebsiteId());
        $this->assertEmpty($result->getLocationId());
        $this->assertEmpty($result->getVehicleId());
        $this->assertEquals(self::LEAD_FIRST_NAME, $result->getFirstName());
        $this->assertEquals(self::LEAD_LAST_NAME, $result->getLastName());
        $this->assertEquals(self::LEAD_TELEPHONE, $result->getPhone());
        $this->assertEquals(self::LEAD_EMAIL, $result->getEmail());
        $this->assertEquals(self::OFFICE_INFO_NAME, $result->getAddrStreet());
        $this->assertEquals(self::OFFICE_INFO_CITY, $result->getAddrCity());
        $this->assertEquals(self::OFFICE_INFO_STATE, $result->getAddrState());
        $this->assertEquals(self::OFFICE_INFO_ZIP, $result->getAddrZip());
        $this->assertEquals(self::CUSTOMER_COMMENTS, $result->getComments());
    }

    /**
     * @group CRM
     * @covers ::getLead
     *
     * @dataProvider notValidHtmlParamsProvider
     */
    public function testGetLeadWithNotValidHtml($dealer, $email)
    {
        Log::shouldReceive('error');

        $this->expectException(InvalidImportFormatException::class);

        /** @var HtmlService $service */
        $service = $this->app->make(HtmlService::class);
        $service->getLead($dealer, $email);
    }

    /**
     * @return array
     */
    public function validHtmlParamsProvider(): array
    {
        /** @var User $dealer */
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = self::DEALER_ID;

        /** @var Website $website */
        $website = $this->getEloquentMock(Website::class);
        $website->id = self::WEBSITE_ID;

        $this->initHasOneRelation($dealer, 'website', $website);

        $html = self::VALID_HTML;

        $selfReflection = new \ReflectionClass(self::class);

        foreach ($selfReflection->getConstants() as $name => $value) {
            $html = str_replace("%{$name}%", $value, $html);
        }

        $email = new ParsedEmail();
        $email->setBody($html);

        return [[$dealer, $email]];
    }

    /**
     * @return array
     */
    public function notValidHtmlParamsProvider(): array
    {
        $dealer = $this->getEloquentMock(User::class);

        $email = new ParsedEmail();
        $email->setBody(self::NOT_VALID_HTML);

        return [[$dealer, $email]];
    }
}
