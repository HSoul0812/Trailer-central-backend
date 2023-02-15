<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Inventory;

use App\Http\Controllers\v1\Inventory\InventoryController;
use App\Http\Middleware\Inventory\CreateInventoryPermissionMiddleware;
use App\Http\Requests\Inventory\GetInventoryHistoryRequest;
use App\Jobs\ElasticSearch\Cache\InvalidateCacheJob;
use App\Models\CRM\Dms\Customer\CustomerInventory;
use App\Models\CRM\User\Customer;
use App\Models\Inventory\EntityType;
use App\Models\Inventory\Inventory;
use App\Models\User\AuthToken;
use App\Models\User\Interfaces\PermissionsInterface;
use Dingo\Api\Exception\ResourceException;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Scout\MakeSearchable;
use Tests\database\seeds\Inventory\InventoryHistorySeeder;
use Tests\database\seeds\Inventory\InventorySeeder;
use Tests\database\seeds\User\GeolocationSeeder;
use Tests\TestCase;
use TypeError;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Inventory\GenerateOverlayImageJob;
use App\Models\Inventory\InventoryImage;
use App\Models\Inventory\Image;

/**
 * Class InventoryControllerTest
 *
 * @package Tests\Integration\Http\Controllers\Inventory
 *
 * @coversDefaultClass \App\Http\Controllers\v1\Inventory\InventoryController
 */
class InventoryControllerTest extends TestCase
{
    use WithFaker;

    const API_INVENTORY_TITLES = '/api/inventory/get_all_titles';
    const API_INVENTORY_EXISTS = '/api/inventory/exists';
    const TEST_UPLOADED_IMAGE_URL = 'https://placehold.co/700.png';

    public function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->setCacheInvalidation(true);
        Inventory::enableSearchSyncing();
    }

    protected function tearDown(): void
    {
        $this->setCacheInvalidation(true);
        Inventory::enableSearchSyncing();

        parent::tearDown();
    }

    /**
     * Tests that SUT is throwing the correct exception when some query parameter is invalid
     *
     * @typeOfTest IntegrationTestCase
     *
     * @dataProvider invalidQueryParametersProvider
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers       InventoryController::history
     *
     * @group DMS
     * @group DMS_INVENTORY
     */
    public function testHistoryInvalidParameters(
        array $params,
        string $expectedException,
        string $expectedExceptionMessage,
        ?string $firstExpectedErrorMessage
    ): void {
        $inventoryHistorySeeder = new InventoryHistorySeeder();

        // Given I have a collection of inventory transactions
        $inventoryHistorySeeder->seed();

        // When I call the history action
        // Then I expect see that one exception have been thrown with a specific message
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $request = new GetInventoryHistoryRequest($params);
        $controller = app()->make(InventoryController::class);

        try {
            $controller->history($params['inventory_id'] ?? null, $request);
        } catch (TypeError $exception) {
            self::assertStringContainsString($expectedExceptionMessage, $exception->getMessage());

            throw $exception;
        } catch (ResourceException $exception) {
            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());

            throw $exception;
        }

        $inventoryHistorySeeder->cleanUp();
    }

    /**
     * @covers ::create
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @dataProvider inventoryDataProvider
     *
     * @param array $inventoryParams
     */
    public function testCreateWithDescriptionSuccess(array $inventoryParams)
    {
        $seeder = new InventorySeeder();
        $seeder->seed();

        $this->assertDatabaseMissing('inventory', ['dealer_id' => $seeder->dealer->dealer_id]);

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['manufacturer'] = $seeder->inventoryMfg->name;
        $inventoryParams['brand'] = $seeder->brand->name;
        $inventoryParams['category'] = $seeder->category->legacy_category;

        $response = $this->json('PUT', '/api/inventory', $inventoryParams, $this->getSeederAccessToken($seeder));

        $responseJson = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $responseJson);
        $this->assertArrayHasKey('status', $responseJson['response']);
        $this->assertArrayHasKey('data', $responseJson['response']);
        $this->assertArrayHasKey('id', $responseJson['response']['data']);
        $this->assertSame('success', $responseJson['response']['status']);

        Queue::assertPushed(MakeSearchable::class, 1);
        Queue::assertPushed(InvalidateCacheJob::class, 1);

        $inventory = Inventory::find($responseJson['response']['data']['id']);

        $doc = new \DOMDocument();
        $doc->loadHTML($inventory['description_html']);

        foreach ($inventoryParams['description_html_assertion'] ?? [] as $item) {
            $tagName = $item['tagName'];
            $domElements = $doc->getElementsByTagName($tagName);

            $this->assertSame($item['count'], count($domElements));

            switch ($item['type']) {
                case 'same':
                    $this->assertSame($domElements->item($item['index'])->textContent, $item['search']);

                    break;
                case 'contains':
                    $this->assertStringContainsStringIgnoringCase(
                        $item['search'],
                        $domElements->item($item['index'])->textContent
                    );

                    break;
            }
        }

        $seeder->cleanUp();
    }

    /**
     * @covers ::create
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @dataProvider inventoryDataProvider
     *
     * @param array $inventoryParams
     */
    public function testDealerUserCreate(array $inventoryParams)
    {
        $inventoryParams = $this->getInventoryDataWithoutExtraInfo($inventoryParams);

        $seederParams = [
            'userType' => AuthToken::USER_TYPE_DEALER_USER,
            'permissions' => [[
                'feature' => PermissionsInterface::INVENTORY,
                'permission_level' => PermissionsInterface::SUPER_ADMIN_PERMISSION,
            ]],
        ];

        $seeder = new InventorySeeder($seederParams);

        $seeder->seed();

        $this->assertDatabaseMissing('inventory', ['dealer_id' => $seeder->dealer->dealer_id]);

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['manufacturer'] = $seeder->inventoryMfg->name;
        $inventoryParams['brand'] = $seeder->brand->name;
        $inventoryParams['category'] = $seeder->category->legacy_category;

        $response = $this->json('PUT', '/api/inventory', $inventoryParams, $this->getSeederAccessToken($seeder));

        $response->assertStatus(201);

        Queue::assertPushed(MakeSearchable::class, 1);
        Queue::assertPushed(InvalidateCacheJob::class, 1);

        $responseJson = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $responseJson);
        $this->assertArrayHasKey('status', $responseJson['response']);
        $this->assertArrayHasKey('data', $responseJson['response']);
        $this->assertArrayHasKey('id', $responseJson['response']['data']);
        $this->assertSame('success', $responseJson['response']['status']);

        $this->assertDatabaseHas('inventory', $inventoryParams);

        $seeder->cleanUp();
    }

    /**
     * @covers ::create
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     *
     * @dataProvider wrongInventoryDataProvider
     */
    public function testWillValidateAsExpected(array $parameters, string $expectedMessage, array $expectedErrorMessages): void
    {
        $seederParams = [
            'userType' => AuthToken::USER_TYPE_DEALER_USER,
            'permissions' => [[
                'feature' => PermissionsInterface::INVENTORY,
                'permission_level' => PermissionsInterface::SUPER_ADMIN_PERMISSION,
            ]],
        ];

        $seeder = new InventorySeeder($seederParams);

        $seeder->seed();

        $this->assertDatabaseMissing('inventory', ['dealer_id' => $seeder->dealer->dealer_id]);

        $parameters['dealer_id'] = $seeder->dealer->dealer_id;
        $parameters['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $parameters['manufacturer'] = $seeder->inventoryMfg->name;
        $parameters['brand'] = is_callable($parameters['brand']) ? $parameters['brand']($seeder) : $parameters['brand'];
        $parameters['category'] = $seeder->category->legacy_category;

        $response = $this->json('PUT', '/api/inventory', $parameters, $this->getSeederAccessToken($seeder));

        $response->assertStatus(422);

        $responseJson = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $responseJson);
        self::assertArrayHasKey('errors', $responseJson);
        self::assertSame($expectedMessage, $responseJson['message']);
        self::assertSame($expectedErrorMessages, $responseJson['errors']);

        $seeder->cleanUp();
    }

    /**
     * @covers ::create
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @dataProvider inventoryDataProvider
     *
     * @param array $inventoryParams
     */
    public function testDealerUserCreateWithoutAdminPermission(array $inventoryParams)
    {
        $seederParams = [
            'userType' => AuthToken::USER_TYPE_DEALER_USER,
            'permissions' => [[
                'feature' => PermissionsInterface::INVENTORY,
                'permission_level' => PermissionsInterface::CAN_SEE_AND_CHANGE_PERMISSION,
            ]],
        ];

        $seeder = new InventorySeeder($seederParams);

        $seeder->seed();

        $this->assertDatabaseMissing('inventory', ['dealer_id' => $seeder->dealer->dealer_id]);

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['manufacturer'] = $seeder->inventoryMfg->name;
        $inventoryParams['brand'] = $seeder->brand->name;
        $inventoryParams['category'] = $seeder->category->legacy_category;

        $expectedInventoryParams = $this->getInventoryDataWithoutExtraInfo($inventoryParams);

        foreach (CreateInventoryPermissionMiddleware::SUPER_ADMIN_FIELDS as $field) {
            unset($expectedInventoryParams[$field]);
        }

        $response = $this->json('PUT', '/api/inventory', $inventoryParams, $this->getSeederAccessToken($seeder));

        $response->assertStatus(201);

        Queue::assertPushed(MakeSearchable::class, 1);
        Queue::assertPushed(InvalidateCacheJob::class, 1);

        $responseJson = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $responseJson);
        $this->assertArrayHasKey('status', $responseJson['response']);
        $this->assertArrayHasKey('data', $responseJson['response']);
        $this->assertArrayHasKey('id', $responseJson['response']['data']);
        $this->assertSame('success', $responseJson['response']['status']);

        $this->assertDatabaseHas('inventory', $expectedInventoryParams);

        $seeder->cleanUp();
    }

    /**
     * @covers ::create
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     */
    public function testCreateWithWrongAccessToken()
    {
        $response = $this->json('PUT', '/api/inventory', [], ['access-token' => 'wrong_access_token']);

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');
    }

    /**
     * @covers ::create
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     */
    public function testCreateWithoutAccessToken()
    {
        $response = $this->json('PUT', '/api/inventory', []);

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');
    }

    /**
     * @covers ::exists
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     */
    public function testExists()
    {
        $seeder = new InventorySeeder(['withInventory' => true]);
        $seeder->seed();

        $this->assertDatabaseHas('inventory', ['inventory_id' => $seeder->inventory->inventory_id]);

        // Get Exists Params
        $existsParams = [
            'dealer_id' => $seeder->dealer->dealer_id,
            'stock' => $seeder->inventory->stock,
        ];
        $response = $this->json('GET', self::API_INVENTORY_EXISTS, $existsParams, $this->getSeederAccessToken($seeder));

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventory', $existsParams);

        $responseJson = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $responseJson);
        $this->assertArrayHasKey('status', $responseJson['response']);
        $this->assertArrayHasKey('data', $responseJson['response']);
        $this->assertSame('success', $responseJson['response']['status']);
        $this->assertTrue($responseJson['response']['data']);

        $seeder->cleanUp();
    }

    /**
     * @covers ::exists
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     */
    public function testExistsFalse()
    {
        $seeder = new InventorySeeder(['withInventory' => true]);
        $seeder->seed();

        // Insert Into DB
        $this->assertDatabaseHas('inventory', ['inventory_id' => $seeder->inventory->inventory_id]);

        // Get Exists Params
        $existsParams = [
            'dealer_id' => $seeder->dealer->dealer_id,
            'stock' => $seeder->inventory->stock . '_unused',
        ];
        $response = $this->json('GET', self::API_INVENTORY_EXISTS, $existsParams, $this->getSeederAccessToken($seeder));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('inventory', $existsParams);

        $responseJson = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $responseJson);
        $this->assertArrayHasKey('status', $responseJson['response']);
        $this->assertArrayHasKey('data', $responseJson['response']);
        $this->assertSame('success', $responseJson['response']['status']);
        $this->assertFalse($responseJson['response']['data']);

        $seeder->cleanUp();
    }

    /**
     * @covers ::exists
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     *
     * @dataProvider inventoryDataProvider
     *
     * @param array $inventoryParams
     */
    public function testExistsWithInventory(array $inventoryParams)
    {
        $seeder = new InventorySeeder();
        $seeder->seed();

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['manufacturer'] = $seeder->inventoryMfg;
        $inventoryParams['brand'] = $seeder->brand;
        $inventoryParams['category'] = $seeder->category;

        $inventoryParams = $this->getInventoryDataWithoutExtraInfo($inventoryParams);

        // Insert Into DB
        $inventory = factory(Inventory::class)->create($inventoryParams);
        $this->assertDatabaseHas('inventory', ['inventory_id' => $inventory->inventory_id]);

        // Insert Another Into DB
        $inventoryParams['stock'] .= '2';
        $inventory2 = factory(Inventory::class)->create($inventoryParams);
        $this->assertDatabaseHas('inventory', ['inventory_id' => $inventory2->inventory_id]);

        // Get Exists Params
        $existsParams = [
            'dealer_id' => $inventoryParams['dealer_id'],
            'inventory_id' => $inventory->inventory_id,
            'stock' => $inventoryParams['stock'],
        ];
        $response = $this->json('GET', self::API_INVENTORY_EXISTS, $existsParams, $this->getSeederAccessToken($seeder));

        $response->assertStatus(200);

        unset($existsParams['inventory_id']);
        $this->assertDatabaseHas('inventory', $existsParams);

        $responseJson = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $responseJson);
        $this->assertArrayHasKey('status', $responseJson['response']);
        $this->assertArrayHasKey('data', $responseJson['response']);
        $this->assertSame('success', $responseJson['response']['status']);
        $this->assertTrue($responseJson['response']['data']);

        $seeder->cleanUp();
    }

    /**
     * @covers ::exists
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     *
     * @dataProvider inventoryDataProvider
     *
     * @param array $inventoryParams
     */
    public function testExistsWithInventoryFalse(array $inventoryParams)
    {
        $seeder = new InventorySeeder(['withInventory' => true]);
        $seeder->seed();

        $inventoryParams = $this->getInventoryDataWithoutExtraInfo($inventoryParams);

        // Insert Into DB
        $inventory = factory(Inventory::class)->create($inventoryParams);
        $this->assertDatabaseHas('inventory', ['inventory_id' => $inventory->inventory_id]);

        // Get Exists Params
        $existsParams = [
            'dealer_id' => $seeder->dealer->dealer_id,
            'inventory_id' => $inventory->inventory_id,
            'stock' => $inventoryParams['stock'] . '_unused',
        ];
        $response = $this->json('GET', self::API_INVENTORY_EXISTS, $existsParams, $this->getSeederAccessToken($seeder));

        $response->assertStatus(200);

        unset($existsParams['inventory_id']);
        $this->assertDatabaseMissing('inventory', $existsParams);

        $responseJson = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $responseJson);
        $this->assertArrayHasKey('status', $responseJson['response']);
        $this->assertArrayHasKey('data', $responseJson['response']);
        $this->assertSame('success', $responseJson['response']['status']);
        $this->assertFalse($responseJson['response']['data']);

        $seeder->cleanUp();
    }

    /**
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     *
     * @return void
     */
    public function testDeliveryPrice()
    {
        $seeder = new InventorySeeder(['withInventory' => true]);
        $seeder->seed();
        $inventoryId = $seeder->inventory->getKey();
        $response = $this->json('GET', "/api/inventory/$inventoryId/delivery_price", [], $this->getSeederAccessToken($seeder));
        $response->assertJson([
            'response' => [
                'status' => 'success',
                'fee' => $seeder->dealerLocationMileageFee->fee_per_mile * 96.893,
            ],
        ]);
        $seeder->cleanUp();
    }

    /**
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     *
     * @return void
     */
    public function testDeliveryPriceToZip()
    {
        $seeder = new InventorySeeder(['withInventory' => true]);
        $locationSeeder = new GeolocationSeeder([
            'latitude' => 11,
            'longitude' => 11,
        ]);

        $seeder->seed();
        $locationSeeder->seed();
        $inventoryId = $seeder->inventory->getKey();
        $response = $this->json('GET', "/api/inventory/$inventoryId/delivery_price?tozip=" . $locationSeeder->location->zip, [], $this->getSeederAccessToken($seeder));
        $response->assertJson([
            'response' => [
                'status' => 'success',
                'fee' => $seeder->dealerLocationMileageFee->fee_per_mile * 96.893,
            ],
        ]);

        $locationSeeder->cleanUp();
        $seeder->cleanUp();
    }

    /**
     * Examples of invalid query parameters with their respective expected exception class name and its messages
     *
     * @return array[]
     */
    public function invalidQueryParametersProvider(): array
    {
        return [                                // array $parameters, int $expectedTotal, int $expectedLastPage, string $expectedCustomerName
            'InventoryId must be an integer' => [[], TypeError::class, 'Argument 1 passed to App\Http\Controllers\v1\Inventory\InventoryController::history() must be of the type int, null given', null],
            'Customer must to be an integer' => [['inventory_id' => 666999, 'customer_id' => [666999]], ResourceException::class, 'Validation Failed', 'The customer id needs to be an integer.'],
            'Search term invalid' => [['inventory_id' => 666999, 'search_term' => ['Truck']], ResourceException::class, 'Validation Failed', 'The search term must be a string.'],
            'Sort invalid' => [['inventory_id' => 666999, 'sort' => '-with'], ResourceException::class, 'Validation Failed', 'The selected sort is invalid.'],
            'Per page invalid (min)' => [['inventory_id' => 666999, 'per_page' => -10], ResourceException::class, 'Validation Failed', 'The per page must be at least 1.'],
            'Per page invalid (max)' => [['inventory_id' => 666999, 'per_page' => 5000000], ResourceException::class, 'Validation Failed', 'The per page may not be greater than 2000.'],
        ];
    }

    /**
     * @return array
     */
    public function inventoryDataProvider(): array
    {
        return [
            'inventory_cdw_669' => [[
                'entity_type_id' => 1, // CDW-669 Sample
                'active' => true,
                'title' => 'test_title',
                'stock' => 'test_stock',
                'model' => 'test_model',
                'qb_item_category_id' => 111,
                'description' => <<< SQL
#### OVERVIEW\\\\n\\\\n\\\\n2019 Forest River Grey Wolf 29BH \\\\nTravel Trailer RV / Sleeps 10 / Dry: 6336 lbs / Bunk Model \\\\n\\\\n\\\\nForest River has been a trusted name that provides reliable and affordable RVs! This pre-owned 2019 Forest River Grey Wolf 29BH appeals to a broad breadth of RV lifestyles ranging from weekend use all the way to extended use and just about every use in between. Designed to fit your family''s budget this exciting Grey Wolf provides an abundance of value. It''s time to make camping great again. So let''s schedule an appointment today to see this exciting 2019 Grey Wolf 29BH. Are you ready to go camping? So it''s time to get hitched up and on the road to your next camping adventure today. \\\\n\\\\nThis 2019 Forest River Grey Wolf 29BH is priced to sell today. Yes you get great instant savings. As well as a full 90 Day Certified Pre-Owned RV Warranty for added peace of mind. So drive a little save a lot at Central PA''s largest towable RV dealership. We are waiting to hear from you call 800-722-1236 today. Or text us at 717-667-1400\\. So fill out the contact form for more information on this exciting RV. Therefore if you have a current trailer that you enjoy camping in fill out our RV trade-in form and we will provide you with honest trade-in values. So click or call today. Financing may be available for qualified buyers. Please contact our Business Department for all financing information. Also additional extended service contracts are available for sale. RV buying made easy at Lerch RV.
SQL,
                'description_html' => <<< HTML
<h4>OVERVIEW</h4><br />
<p>2019 Forest River Grey Wolf 29BH </p><br />
<p>Travel Trailer RV / Sleeps 10 / Dry: 6336 lbs / Bunk Model </p><br />
<p>Forest River has been a trusted name that provides reliable and affordable RVs! This pre-owned 2019 Forest River Grey Wolf 29BH appeals to a broad breadth of RV lifestyles ranging from weekend use all the way to extended use and just about every use in between. Designed to fit your family''s budget this exciting Grey Wolf provides an abundance of value. It''s time to make camping great again. So let''s schedule an appointment today to see this exciting 2019 Grey Wolf 29BH. Are you ready to go camping? So it''s time to get hitched up and on the road to your next camping adventure today. </p><br />
<p>This 2019 Forest River Grey Wolf 29BH is priced to sell today. Yes you get great instant savings. As well as a full 90 Day Certified Pre-Owned RV Warranty for added peace of mind. So drive a little save a lot at Central PA''s largest towable RV dealership. We are waiting to hear from you call 800-722-1236 today. Or text us at 717-667-1400. So fill out the contact form for more information on this exciting RV. Therefore if you have a current trailer that you enjoy camping in fill out our RV trade-in form and we will provide you with honest trade-in values. So click or call today. Financing may be available for qualified buyers. Please contact our Business Department for all financing information. Also additional extended service contracts are available for sale. RV buying made easy at Lerch RV.</p>

HTML,
                'status' => 1,
                'availability' => 'available',
                'is_consignment' => true,
                'video_embed_code' => 'some_code',
                'vin' => 'test_vin',
                'msrp_min' => 22,
                'msrp' => 33,
                'price' => 44,
                'sales_price' => 66,
                'use_website_price' => true,
                'website_price' => 77,
                'dealer_price' => 88,
                'monthly_payment' => 99,
                'year' => 2020,
                'condition' => 'new',
                'length' => 111,
                'width' => 222,
                'height' => 333,
                'weight' => 444,
                'gvwr' => 555,
                'axle_capacity' => 1,
                'cost_of_unit' => 100,
                'true_cost' => 777,
                'cost_of_shipping' => 'test_cost_of_shipping',
                'cost_of_prep' => 'test_cost_of_prep',
                'total_of_cost' => 'test_total_of_cost',
                'pac_amount' => 5555,
                'pac_type' => 'percent',
                'minimum_selling_price' => 'test_minimum_selling_price',
                'notes' => 'some_notes',
                'show_on_ksl' => true,
                'show_on_racingjunk' => true,
                'show_on_website' => true,
                'overlay_enabled' => true,
                'is_special' => true,
                'is_featured' => true,
                'is_archived' => false,
                'archived_at' => null,
                'broken_video_embed_code' => true,
                'showroom_id' => 454,
                'coordinates_updated' => 55,
                'payload_capacity' => 789,
                'height_display_mode' => 'inches',
                'width_display_mode' => 'inches',
                'length_display_mode' => 'inches',
                'width_inches' => 987,
                'height_inches' => 654,
                'length_inches' => 321,
                'show_on_rvtrader' => true,
                'chosen_overlay' => 'test_chosen_overlay',
                'fp_vendor' => 999,
                'fp_balance' => 22.33,
                'fp_paid' => true,
                'fp_interest_paid' => 983.22,
                'l_holder' => 'test_l_holder',
                'l_attn' => 'test_l_attn',
                'l_name_on_account' => 'test_l_name_on_account',
                'l_address' => 'test_l_address',
                'l_account' => 'test_l_account',
                'l_city' => 'test_l_city',
                'l_state' => 'test_l_state',
                'l_zip_code' => 'test_l_zip_code',
                'l_payoff' => 99.66,
                'l_phone' => 'test_l_phone',
                'l_paid' => true,
                'l_fax' => 'test_l_fax',
                'bill_id' => 357,
                'send_to_quickbooks' => true,
                'is_floorplan_bill' => true,
                'integration_item_hash' => 'test_integration_item_hash',
                'integration_images_hash' => 'test_integration_images_hash',
                'non_serialized' => true,
                'hidden_price' => 9911.22,
                'has_stock_images' => true,
                'show_on_auction123' => false,
                'description_html_assertion' => [
                    [
                        'index' => 0,
                        'tagName' => 'h4',
                        'search' => 'OVERVIEW',
                        'count' => 1,
                        'type' => 'same',
                    ],
                    [
                        'index' => 0,
                        'tagName' => 'p',
                        'search' => '2019 Forest River Grey Wolf 29BH ',
                        'count' => 4,
                        'type' => 'same',
                    ],
                    [
                        'index' => 2,
                        'tagName' => 'p',
                        'search' => '2019 Grey Wolf 29BH',
                        'count' => 4,
                        'type' => 'contains',
                    ],
                    [
                        'index' => 3,
                        'tagName' => 'p',
                        'search' => '717-667-1400',
                        'count' => 4,
                        'type' => 'contains',
                    ],
                ],
            ]],
            'inventory_cdw_824_1' => [[
                'entity_type_id' => 1, // CDW-824 Sample
                'active' => true,
                'title' => 'test_title',
                'stock' => 'test_stock',
                'model' => 'test_model',
                'qb_item_category_id' => 111,
                'description' => <<< STRING
Stock Number: AL1879 \\\\

Trailer Specs:
Overall: Length: 14'7" Width: 7'8"
Interior/Bed: Length: 10' Width: 68"
Weight: 675 lbs\\. \\| GVWR: 3\\,500 lbs\\. \\| Payload: 2\\,825 lbs\\. \\\\

Axle(s): 3,500 lb. Dexter Torsion Idler Axle w/ EZ Lube Hubs
Tire/Wheel: ST205/75R14 Aluminum Wheel \\| Load Range C
Coupler: Type: Bumper Pull \\| Size 2" \\| 4\\-Way 12V Connector \\\\

5 Year Limited Warranty \\\\

Standard Features:
Aluminum Fenders
Extruded Aluminum Decking
7" Heavy Duty Frame Rail
6" Extruded Front Retaining Bumper
(4) Stake Pockets
(4) Tie Down Loops
Swivel Tongue Jack 1,200# Capacity
LED Lighting Package
Safety Chains \\\\

WASATCH TRAILER SALES - LARGEST TRAILER SELECTION IN UTAH
Springville: \\(801\\) 528\\-1581 \\| 1180 S 2000 W\\, Springville UT\\, 84663
View More Inventory: [www.wasatchtrailer.com](http://www.wasatchtrailer.com/)

STRING,
                'description_html' => <<< HTML
<p>Stock Number: AL1879 <br></p><br />
<p>Trailer Specs:<br />
Overall: Length: 14'7&quot; Width: 7'8&quot;<br />
Interior/Bed: Length: 10' Width: 68&quot;<br />
Weight: 675 lbs. | GVWR: 3,500 lbs. | Payload: 2,825 lbs. <br></p><br />
<p>Axle(s): 3,500 lb. Dexter Torsion Idler Axle w/ EZ Lube Hubs<br />
Tire/Wheel: ST205/75R14 Aluminum Wheel | Load Range C<br />
Coupler: Type: Bumper Pull | Size 2&quot; | 4-Way 12V Connector <br></p><br />
<p>5 Year Limited Warranty <br></p><br />
<p>Standard Features:<br />
Aluminum Fenders<br />
Extruded Aluminum Decking<br />
7&quot; Heavy Duty Frame Rail<br />
6&quot; Extruded Front Retaining Bumper<br />
(4) Stake Pockets<br />
(4) Tie Down Loops<br />
Swivel Tongue Jack 1,200# Capacity<br />
LED Lighting Package<br />
Safety Chains <br></p><br />
<p>WASATCH TRAILER SALES - LARGEST TRAILER SELECTION IN UTAH<br />
Springville: (801) 528-1581 | 1180 S 2000 W, Springville UT, 84663<br />
View More Inventory: <a href="http://www.wasatchtrailer.com/">www.wasatchtrailer.com</a></p>

HTML,
                'status' => 1,
                'availability' => 'available',
                'is_consignment' => true,
                'video_embed_code' => 'some_code',
                'vin' => 'test_vin',
                'msrp_min' => 22,
                'msrp' => 33,
                'price' => 44,
                'sales_price' => 66,
                'use_website_price' => true,
                'website_price' => 77,
                'dealer_price' => 88,
                'monthly_payment' => 99,
                'year' => 2020,
                'condition' => 'new',
                'length' => 111,
                'width' => 222,
                'height' => 333,
                'weight' => 444,
                'gvwr' => 555,
                'axle_capacity' => 1,
                'cost_of_unit' => 100,
                'true_cost' => 777,
                'cost_of_shipping' => 'test_cost_of_shipping',
                'cost_of_prep' => 'test_cost_of_prep',
                'total_of_cost' => 'test_total_of_cost',
                'pac_amount' => 5555,
                'pac_type' => 'percent',
                'minimum_selling_price' => 'test_minimum_selling_price',
                'notes' => 'some_notes',
                'show_on_ksl' => true,
                'show_on_racingjunk' => true,
                'show_on_website' => true,
                'overlay_enabled' => true,
                'is_special' => true,
                'is_featured' => true,
                'is_archived' => false,
                'archived_at' => null,
                'broken_video_embed_code' => true,
                'showroom_id' => 454,
                'coordinates_updated' => 55,
                'payload_capacity' => 789,
                'height_display_mode' => 'inches',
                'width_display_mode' => 'inches',
                'length_display_mode' => 'inches',
                'width_inches' => 987,
                'height_inches' => 654,
                'length_inches' => 321,
                'show_on_rvtrader' => true,
                'chosen_overlay' => 'test_chosen_overlay',
                'fp_vendor' => 999,
                'fp_balance' => 22.33,
                'fp_paid' => true,
                'fp_interest_paid' => 983.22,
                'l_holder' => 'test_l_holder',
                'l_attn' => 'test_l_attn',
                'l_name_on_account' => 'test_l_name_on_account',
                'l_address' => 'test_l_address',
                'l_account' => 'test_l_account',
                'l_city' => 'test_l_city',
                'l_state' => 'test_l_state',
                'l_zip_code' => 'test_l_zip_code',
                'l_payoff' => 99.66,
                'l_phone' => 'test_l_phone',
                'l_paid' => true,
                'l_fax' => 'test_l_fax',
                'bill_id' => 357,
                'send_to_quickbooks' => true,
                'is_floorplan_bill' => true,
                'integration_item_hash' => 'test_integration_item_hash',
                'integration_images_hash' => 'test_integration_images_hash',
                'non_serialized' => true,
                'hidden_price' => 9911.22,
                'has_stock_images' => true,
                'show_on_auction123' => false,
                'description_html_assertion' => [
                    [
                        'index' => 0,
                        'tagName' => 'p',
                        'search' => 'Stock Number: AL1879',
                        'count' => 6,
                        'type' => 'contains',
                    ],
                    [
                        'index' => 2,
                        'tagName' => 'p',
                        'search' => 'Axle',
                        'count' => 6,
                        'type' => 'contains',
                    ],
                    [
                        'index' => 3,
                        'tagName' => 'p',
                        'search' => '5 Year Limited Warranty',
                        'count' => 6,
                        'type' => 'contains',
                    ],
                    [
                        'index' => 5,
                        'tagName' => 'p',
                        'search' => 'WASATCH TRAILER SALES',
                        'count' => 6,
                        'type' => 'contains',
                    ],
                ],
            ]],
            'inventory_cdw_824_2' => [[
                'entity_type_id' => 1, // CDW-824 Sample
                'active' => true,
                'title' => 'test_title',
                'stock' => 'test_stock',
                'model' => 'test_model',
                'qb_item_category_id' => 111,
                'description' => <<< STRING
Stock Number: AL1879

Trailer Specs:
Overall: Length: 14'7" Width: 7'8"
Interior/Bed: Length: 10' Width: 68"
Weight: 675 lbs\\. \\| GVWR: 3\\,500 lbs\\. \\| Payload: 2\\,825 lbs\\.
Axle(s): 3,500 lb. Dexter Torsion Idler Axle w/ EZ Lube Hubs
Tire/Wheel: ST205/75R14 Aluminum Wheel \\| Load Range C
Coupler: Type: Bumper Pull \\| Size 2" \\| 4\\-Way 12V Connector

5 Year Limited Warranty

Standard Features:
Aluminum Fenders
Extruded Aluminum Decking
7" Heavy Duty Frame Rail
6" Extruded Front Retaining Bumper
(4) Stake Pockets
(4) Tie Down Loops
Swivel Tongue Jack 1,200# Capacity
LED Lighting Package

Safety Chains
WASATCH TRAILER SALES - LARGEST TRAILER SELECTION IN UTAH
Springville: \\(801\\) 528\\-1581 \\| 1180 S 2000 W\\, Springville UT\\, 84663
View More Inventory: [https://www.wasatchtrailer.com](https://www.wasatchtrailer.com)
STRING,
                'description_html' => <<< HTML
<p>Stock Number: AL1879</p><br />
<p>Trailer Specs:<br />
Overall: Length: 14'7&quot; Width: 7'8&quot;<br />
Interior/Bed: Length: 10' Width: 68&quot;<br />
Weight: 675 lbs. | GVWR: 3,500 lbs. | Payload: 2,825 lbs.<br />
Axle(s): 3,500 lb. Dexter Torsion Idler Axle w/ EZ Lube Hubs<br />
Tire/Wheel: ST205/75R14 Aluminum Wheel | Load Range C<br />
Coupler: Type: Bumper Pull | Size 2&quot; | 4-Way 12V Connector</p><br />
<p>5 Year Limited Warranty</p><br />
<p>Standard Features:<br />
Aluminum Fenders<br />
Extruded Aluminum Decking<br />
7&quot; Heavy Duty Frame Rail<br />
6&quot; Extruded Front Retaining Bumper<br />
(4) Stake Pockets<br />
(4) Tie Down Loops<br />
Swivel Tongue Jack 1,200# Capacity<br />
LED Lighting Package</p><br />
<p>Safety Chains<br />
WASATCH TRAILER SALES - LARGEST TRAILER SELECTION IN UTAH<br />
Springville: (801) 528-1581 | 1180 S 2000 W, Springville UT, 84663<br />
View More Inventory: <a href="https://www.wasatchtrailer.com">https://www.wasatchtrailer.com</a></p>

HTML,
                'status' => 1,
                'availability' => 'available',
                'is_consignment' => true,
                'video_embed_code' => 'some_code',
                'vin' => 'test_vin',
                'msrp_min' => 22,
                'msrp' => 33,
                'price' => 44,
                'sales_price' => 66,
                'use_website_price' => true,
                'website_price' => 77,
                'dealer_price' => 88,
                'monthly_payment' => 99,
                'year' => 2020,
                'condition' => 'new',
                'length' => 111,
                'width' => 222,
                'height' => 333,
                'weight' => 444,
                'gvwr' => 555,
                'axle_capacity' => 1,
                'cost_of_unit' => 100,
                'true_cost' => 777,
                'cost_of_shipping' => 'test_cost_of_shipping',
                'cost_of_prep' => 'test_cost_of_prep',
                'total_of_cost' => 'test_total_of_cost',
                'pac_amount' => 5555,
                'pac_type' => 'percent',
                'minimum_selling_price' => 'test_minimum_selling_price',
                'notes' => 'some_notes',
                'show_on_ksl' => true,
                'show_on_racingjunk' => true,
                'show_on_website' => true,
                'overlay_enabled' => true,
                'is_special' => true,
                'is_featured' => true,
                'is_archived' => false,
                'archived_at' => null,
                'broken_video_embed_code' => true,
                'showroom_id' => 454,
                'coordinates_updated' => 55,
                'payload_capacity' => 789,
                'height_display_mode' => 'inches',
                'width_display_mode' => 'inches',
                'length_display_mode' => 'inches',
                'width_inches' => 987,
                'height_inches' => 654,
                'length_inches' => 321,
                'show_on_rvtrader' => true,
                'chosen_overlay' => 'test_chosen_overlay',
                'fp_vendor' => 999,
                'fp_balance' => 22.33,
                'fp_paid' => true,
                'fp_interest_paid' => 983.22,
                'l_holder' => 'test_l_holder',
                'l_attn' => 'test_l_attn',
                'l_name_on_account' => 'test_l_name_on_account',
                'l_address' => 'test_l_address',
                'l_account' => 'test_l_account',
                'l_city' => 'test_l_city',
                'l_state' => 'test_l_state',
                'l_zip_code' => 'test_l_zip_code',
                'l_payoff' => 99.66,
                'l_phone' => 'test_l_phone',
                'l_paid' => true,
                'l_fax' => 'test_l_fax',
                'bill_id' => 357,
                'send_to_quickbooks' => true,
                'is_floorplan_bill' => true,
                'integration_item_hash' => 'test_integration_item_hash',
                'integration_images_hash' => 'test_integration_images_hash',
                'non_serialized' => true,
                'hidden_price' => 9911.22,
                'has_stock_images' => true,
                'show_on_auction123' => false,
                'description_html_assertion' => [
                    [
                        'index' => 0,
                        'tagName' => 'p',
                        'search' => 'Stock Number: AL1879',
                        'count' => 5,
                        'type' => 'contains',
                    ],
                    [
                        'index' => 1,
                        'tagName' => 'p',
                        'search' => 'Axle',
                        'count' => 5,
                        'type' => 'contains',
                    ],
                    [
                        'index' => 3,
                        'tagName' => 'p',
                        'search' => 'Aluminum Fenders',
                        'count' => 5,
                        'type' => 'contains',
                    ],
                    [
                        'index' => 4,
                        'tagName' => 'p',
                        'search' => 'Springville',
                        'count' => 5,
                        'type' => 'contains',
                    ],
                ],
            ]],
        ];
    }

    /**
     * @return array
     */
    public function wrongInventoryDataProvider(): array
    {
        $this->refreshApplication();
        $this->setUpTraits();

        return [
            'wrong brand when brand is watercraft or RVs' => [
                [
                    'entity_type_id' => $this->faker->randomElement([EntityType::ENTITY_TYPE_WATERCRAFT, EntityType::ENTITY_TYPE_RV]),
                    'active' => true,
                    'title' => 'test_title',
                    'stock' => 'test_stock',
                    'model' => 'test_model',
                    'brand' => 123, // a wrong brand
                    'qb_item_category_id' => 111,
                    'status' => 1,
                    'availability' => 'available',
                    'is_consignment' => true,
                    'video_embed_code' => 'some_code',
                    'vin' => 'test_vin',
                    'msrp_min' => 22,
                    'msrp' => 33,
                    'price' => 44,
                    'sales_price' => 66,
                    'use_website_price' => true,
                    'website_price' => 77,
                    'dealer_price' => 88,
                    'monthly_payment' => 99,
                    'year' => 2020,
                    'condition' => 'new',
                    'length' => 111,
                    'width' => 222,
                    'height' => 333,
                    'weight' => 444,
                    'gvwr' => 555,
                    'axle_capacity' => 1,
                    'cost_of_unit' => 100,
                    'true_cost' => 777,
                    'cost_of_shipping' => 'test_cost_of_shipping',
                    'cost_of_prep' => 'test_cost_of_prep',
                    'total_of_cost' => 'test_total_of_cost',
                    'pac_amount' => 5555,
                    'pac_type' => 'percent',
                    'minimum_selling_price' => 'test_minimum_selling_price',
                    'notes' => 'some_notes',
                    'show_on_ksl' => true,
                    'show_on_racingjunk' => true,
                    'show_on_website' => true,
                    'overlay_enabled' => true,
                    'is_special' => true,
                    'is_featured' => true,
                    'is_archived' => false,
                    'archived_at' => null,
                    'broken_video_embed_code' => true,
                    'showroom_id' => 454,
                    'coordinates_updated' => 55,
                    'payload_capacity' => 789,
                    'height_display_mode' => 'inches',
                    'width_display_mode' => 'inches',
                    'length_display_mode' => 'inches',
                    'width_inches' => 987,
                    'height_inches' => 654,
                    'length_inches' => 321,
                    'show_on_rvtrader' => true,
                    'chosen_overlay' => 'test_chosen_overlay',
                    'fp_vendor' => 999,
                    'fp_balance' => 22.33,
                    'fp_paid' => true,
                    'fp_interest_paid' => 983.22,
                    'l_holder' => 'test_l_holder',
                    'l_attn' => 'test_l_attn',
                    'l_name_on_account' => 'test_l_name_on_account',
                    'l_address' => 'test_l_address',
                    'l_account' => 'test_l_account',
                    'l_city' => 'test_l_city',
                    'l_state' => 'test_l_state',
                    'l_zip_code' => 'test_l_zip_code',
                    'l_payoff' => 99.66,
                    'l_phone' => 'test_l_phone',
                    'l_paid' => true,
                    'l_fax' => 'test_l_fax',
                    'bill_id' => 357,
                    'send_to_quickbooks' => true,
                    'is_floorplan_bill' => true,
                    'integration_item_hash' => 'test_integration_item_hash',
                    'integration_images_hash' => 'test_integration_images_hash',
                    'non_serialized' => true,
                    'hidden_price' => 9911.22,
                    'has_stock_images' => true,
                    'show_on_auction123' => false,
                ],
                'Validation Failed',
                ['brand' => ['validation.inventory_brand_valid']],
            ],
        ];
    }

    /**
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     *
     * @return void
     */
    public function testGetAllInventoryTitlesWithoutCustomerId()
    {
        $seeder = $this->seedInventory();
        $inventory = $seeder->inventory;

        $response = $this->json('GET', self::API_INVENTORY_TITLES, [], $this->getSeederAccessToken($seeder));
        $response->assertJson([
            [
                'children' => [],
                'text' => 'Customer Owned Inventories',
            ],
            [
                'children' => [
                    $this->prepareTitleApiChildrenResponse($inventory),
                ],
                'text' => 'All Inventories',
            ],
        ]);

        $this->cleanUpSeeder($seeder);
    }

    /**
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     *
     * @return void
     */
    public function testGetAllInventoryTitlesWithCustomerId()
    {
        $seeder = $this->seedInventory();
        $inventory = $seeder->inventory;
        $customer = factory(Customer::class)->create([
            'dealer_id' => $inventory->dealer_id,
        ]);
        CustomerInventory::create(['customer_id' => $customer->getKey(), 'inventory_id' => $inventory->getKey()]);
        $seeder2 = $this->seedInventory();
        $inventory2 = factory(Inventory::class)->create([
            'dealer_id' => $inventory->dealer_id,
            'geolocation' => new Point(0, 0),
        ]);

        $apiUrl = self::API_INVENTORY_TITLES . '?customer_id=' . $customer->getKey();

        $response = $this->json('GET', $apiUrl, [], $this->getSeederAccessToken($seeder));
        $response->assertJson([
            [
                'children' => [
                    $this->prepareTitleApiChildrenResponse($inventory),
                ],
                'text' => 'Customer Owned Inventories',
            ],
            [
                'children' => [
                    $this->prepareTitleApiChildrenResponse($inventory),
                    $this->prepareTitleApiChildrenResponse($inventory2),
                ],
                'text' => 'All Inventories',
            ],
        ]);

        $this->cleanUpSeeder($seeder);
        Customer::where('dealer_id', $inventory->dealer_id)->delete();
        $this->cleanUpSeeder($seeder2);
    }

    private function seedInventory(bool $withInventory = true): InventorySeeder
    {
        $seeder = new InventorySeeder(['withInventory' => $withInventory]);
        $seeder->seed();

        return $seeder;
    }

    private function cleanUpSeeder($seeder)
    {
        method_exists($seeder, 'cleanUp') ? $seeder->cleanUp() : null;
    }

    private function getSeederAccessToken($seeder): array
    {
        return [
            'access-token' => $seeder->authToken->access_token ?? '',
        ];
    }

    public function prepareTitleApiChildrenResponse(Inventory $inventory)
    {
        return [
            'id' => $inventory->getKey(),
            'title' => $inventory->title,
            'vin' => $inventory->vin,
        ];
    }

    /**
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     *
     * @return void
     */
    public function testExport()
    {
        Storage::fake('s3');

        $seeder = new InventorySeeder(['withInventory' => true, 'withWebsite' => true]);
        $seeder->seed();
        $inventoryId = $seeder->inventory->getKey();
        $response = $this->json('POST', "/api/inventory/$inventoryId/export", [
            'format' => 'pdf',
        ], $this->getSeederAccessToken($seeder));
        $response->assertJsonPath('response.status', 'success');

        Storage::disk('s3')->exists("inventory-exports/$inventoryId");

        $seeder->cleanUp();
    }

    /**
     * @param array $inventoryParams
     *
     * @return array
     */
    private function getInventoryDataWithoutExtraInfo(array $inventoryParams): array
    {
        return Arr::except($inventoryParams, ['description_html_assertion', 'description_html']);
    }

    /**
     * @covers ::create
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     */
    public function testCreateWithImages()
    {
        $seeder = new InventorySeeder;
        $seeder->seed();

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['entity_type_id'] = 1;
        $inventoryParams['title'] = 'test_title';

        $inventoryParams['new_images'] = [];
        $inventoryParams['new_images'][] = [
            'is_default' => 1,
            'is_secondary' => 0,
            'position' => 1,
            'url' => self::TEST_UPLOADED_IMAGE_URL,
            'was_manually_added' => 1
        ];
        $inventoryParams['new_images'][] = [
            'is_default' => 0,
            'is_secondary' => 1,
            'position' => 2,
            'url' => self::TEST_UPLOADED_IMAGE_URL,
            'was_manually_added' => 1
        ];

        $response = $this->json('PUT', '/api/inventory', $inventoryParams, $this->getSeederAccessToken($seeder));

        $response->assertSuccessful();

        $responseJson = json_decode($response->getContent(), true)['response']['data'];
        $inventoryId = $responseJson['id'];

        $this->assertDatabaseHas(InventoryImage::getTableName(), [
            'inventory_id' => $inventoryId
        ]);

        Queue::assertPushed(GenerateOverlayImageJob::class);
        Queue::assertPushed(InvalidateCacheJob::class, 1);
        Queue::assertPushed(MakeSearchable::class, 1);

        $seeder->cleanUp();
    }

    /**
     * @covers ::create
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     */
    public function testCreateWithNoImage()
    {
        $seeder = new InventorySeeder;
        $seeder->seed();

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['entity_type_id'] = 1;
        $inventoryParams['title'] = 'test_title';

        $response = $this->json('PUT', '/api/inventory', $inventoryParams, $this->getSeederAccessToken($seeder));

        $response->assertSuccessful();

        $responseJson = json_decode($response->getContent(), true)['response']['data'];
        $inventoryId = $responseJson['id'];

        $this->assertDatabaseMissing(InventoryImage::getTableName(), [
            'inventory_id' => $inventoryId
        ]);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
        Queue::assertPushed(InvalidateCacheJob::class, 1);
        Queue::assertPushed(MakeSearchable::class, 1);

        $seeder->cleanUp();

    }

    /**
     * @covers ::update
     * @group Marketing
     * @group Marketing_Overlays
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     */
    public function testUpdateWithNewImages()
    {
        $seeder = new InventorySeeder(['withInventory' => true]);
        $seeder->seed();

        $inventoryParams['title'] = 'test_title';
        $inventoryParams['new_images'] = [];
        $inventoryParams['new_images'][] = [
            'is_default' => 1,
            'is_secondary' => 0,
            'position' => 1,
            'url' => self::TEST_UPLOADED_IMAGE_URL,
            'was_manually_added' => 1
        ];
        $inventoryParams['new_images'][] = [
            'is_default' => 0,
            'is_secondary' => 1,
            'position' => 2,
            'url' => self::TEST_UPLOADED_IMAGE_URL,
            'was_manually_added' => 1
        ];

        $response = $this->json('POST', '/api/inventory/'. $seeder->inventory->getKey(),
            $inventoryParams, $this->getSeederAccessToken($seeder));

        $response->assertSuccessful();

        $this->assertDatabaseHas(InventoryImage::getTableName(), [
            'inventory_id' => $seeder->inventory->getKey()
        ]);

        Queue::assertPushed(GenerateOverlayImageJob::class, 1);
        Queue::assertPushed(InvalidateCacheJob::class, 2);
        Queue::assertPushed(MakeSearchable::class, 1);

        $seeder->cleanUp();

    }

    /**
     * @covers ::update
     * @group Marketing
     * @group Marketing_Overlays
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     */
    public function testUpdateWithExistingImages()
    {
        $seeder = new InventorySeeder(['withInventory' => true]);
        $seeder->seed();

        $inventoryParams['title'] = 'test_title';

        $images = factory(Image::class, 2)->create(); $index = 0;
        $images->each(function (Image $image) use ($seeder, &$index, &$inventoryParams): void {
            factory(InventoryImage::class)->create([
                'inventory_id' => $seeder->inventory->inventory_id,
                'image_id' => $image->image_id
            ]);

            $inventoryParams['existing_images'][] = [
                'image_id' => $image->getKey(),
                'is_default' => $index == 0 ? 1 : 0,
                'is_secondary' => $index == 1 ? 1 : 0,
                'position' => $index + 1
            ];

            $index++;
        });

        $response = $this->json('POST', '/api/inventory/'. $seeder->inventory->getKey(), $inventoryParams, $this->getSeederAccessToken($seeder));

        $response->assertSuccessful();

        $this->assertDatabaseHas(InventoryImage::getTableName(), [
            'inventory_id' => $seeder->inventory->getKey()
        ]);

        Queue::assertPushed(GenerateOverlayImageJob::class, 1);
        Queue::assertPushed(InvalidateCacheJob::class, 2);
        Queue::assertPushed(MakeSearchable::class, 1);

        $seeder->cleanUp();

    }

    /**
     * @covers ::update
     * @group Marketing
     * @group Marketing_Overlays
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     */
    public function testUpdateWithNoImage()
    {
        $seeder = new InventorySeeder(['withInventory' => true]);
        $seeder->seed();

        $inventoryParams['title'] = 'test_title';

        $response = $this->json('POST', '/api/inventory/'. $seeder->inventory->getKey(),
            $inventoryParams, $this->getSeederAccessToken($seeder));

        $response->assertSuccessful();

        $this->assertDatabaseMissing(InventoryImage::getTableName(), [
            'inventory_id' => $seeder->inventory->getKey()
        ]);

        Queue::assertNotPushed(GenerateOverlayImageJob::class, 1);
        Queue::assertPushed(InvalidateCacheJob::class, 2);
        Queue::assertPushed(MakeSearchable::class, 1);

        $seeder->cleanUp();
    }

    /**
     * @covers ::update
     * @group Marketing
     * @group Marketing_Overlays
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     */
    public function testUpdateWithBothImages()
    {
        $seeder = new InventorySeeder(['withInventory' => true]);
        $seeder->seed();

        $inventoryParams['title'] = 'test_title';

        $images = factory(Image::class, 2)->create(); $index = 0;
        $images->each(function (Image $image) use ($seeder, &$index, &$inventoryParams): void {
            factory(InventoryImage::class)->create([
                'inventory_id' => $seeder->inventory->inventory_id,
                'image_id' => $image->image_id
            ]);

            $inventoryParams['existing_images'][] = [
                'image_id' => $image->getKey(),
                'is_default' => $index == 0 ? 1 : 0,
                'is_secondary' => $index == 1 ? 1 : 0,
                'position' => $index + 1
            ];

            $index++;
        });

        $inventoryParams['new_images'][] = [
            'is_default' => 0,
            'is_secondary' => 0,
            'position' => 3,
            'url' => self::TEST_UPLOADED_IMAGE_URL,
            'was_manually_added' => 1
        ];

        $response = $this->json('POST', '/api/inventory/'. $seeder->inventory->getKey(), $inventoryParams, $this->getSeederAccessToken($seeder));

        $response->assertSuccessful();

        $this->assertEquals(3, InventoryImage::where('inventory_id', $seeder->inventory->getKey())->count());

        Queue::assertPushed(GenerateOverlayImageJob::class, 1);
        Queue::assertPushed(InvalidateCacheJob::class, 2);
        Queue::assertPushed(MakeSearchable::class, 1);

        $seeder->cleanUp();
    }
}
