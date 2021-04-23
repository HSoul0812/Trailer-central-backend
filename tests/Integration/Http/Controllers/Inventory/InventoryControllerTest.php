<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Inventory;

use App\Http\Middleware\Inventory\CreateInventoryPermissionMiddleware;
use App\Http\Requests\Inventory\GetInventoryHistoryRequest;
use App\Models\Inventory\Inventory;
use App\Models\User\AuthToken;
use App\Models\User\Interfaces\PermissionsInterface;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\database\seeds\Inventory\InventorySeeder;
use Tests\database\seeds\Inventory\InventoryHistorySeeder;
use App\Http\Controllers\v1\Inventory\InventoryController;
use Tests\TestCase;
use TypeError;

/**
 * Class InventoryControllerTest
 * @package Tests\Integration\Http\Controllers\Inventory
 *
 * @coversDefaultClass \App\Http\Controllers\v1\Inventory\InventoryController
 */
class InventoryControllerTest extends TestCase
{
    /**
     * Tests that SUT is throwing the correct exception when some query parameter is invalid
     *
     * @typeOfTest IntegrationTestCase
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
     */
    public function testHistoryInvalidParameters(
        array $params,
        string $expectedException,
        string $expectedExceptionMessage,
        ?string $firstExpectedErrorMessage
    ): void
    {
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
     * @dataProvider inventoryDataProvider
     *
     * @param array $inventoryParams
     */
    public function testCreate(array $inventoryParams)
    {
        $seeder = new InventorySeeder();
        $seeder->seed();

        $this->assertDatabaseMissing('inventory', ['dealer_id' => $seeder->dealer->dealer_id]);

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['manufacturer'] = $seeder->inventoryMfg->name;
        $inventoryParams['brand'] = $seeder->brand->name;
        $inventoryParams['category'] = $seeder->category->legacy_category;

        $response = $this->json('PUT', '/api/inventory', $inventoryParams, ['access-token' => $seeder->authToken->access_token]);

        $response->assertStatus(200);

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
     * @dataProvider inventoryDataProvider
     *
     * @param array $inventoryParams
     */
    public function testDealerUserCreate(array $inventoryParams)
    {
        $seeder = new InventorySeeder(AuthToken::USER_TYPE_DEALER_USER, [
            [
                'feature' => PermissionsInterface::INVENTORY,
                'permission_level' => PermissionsInterface::SUPER_ADMIN_PERMISSION,
            ]
        ]);

        $seeder->seed();

        $this->assertDatabaseMissing('inventory', ['dealer_id' => $seeder->dealer->dealer_id]);

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['manufacturer'] = $seeder->inventoryMfg->name;
        $inventoryParams['brand'] = $seeder->brand->name;
        $inventoryParams['category'] = $seeder->category->legacy_category;

        $response = $this->json('PUT', '/api/inventory', $inventoryParams, ['access-token' => $seeder->authToken->access_token]);

        $response->assertStatus(200);

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
     * @dataProvider inventoryDataProvider
     *
     * @param array $inventoryParams
     */
    public function testDealerUserCreateWithoutAdminPermission(array $inventoryParams)
    {
        $seeder = new InventorySeeder(AuthToken::USER_TYPE_DEALER_USER, [
            [
                'feature' => PermissionsInterface::INVENTORY,
                'permission_level' => PermissionsInterface::CAN_SEE_AND_CHANGE_PERMISSION,
            ]
        ]);

        $seeder->seed();

        $this->assertDatabaseMissing('inventory', ['dealer_id' => $seeder->dealer->dealer_id]);

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['manufacturer'] = $seeder->inventoryMfg->name;
        $inventoryParams['brand'] = $seeder->brand->name;
        $inventoryParams['category'] = $seeder->category->legacy_category;

        $expectedInventoryParams = $inventoryParams;

        foreach (CreateInventoryPermissionMiddleware::SUPER_ADMIN_FIELDS as $field) {
            unset($expectedInventoryParams[$field]);
        }

        $response = $this->json('PUT', '/api/inventory', $inventoryParams, ['access-token' => $seeder->authToken->access_token]);

        $response->assertStatus(200);

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
     */
    public function testCreateWithWrongAccessToken()
    {
        $response = $this->json('PUT', '/api/inventory/create', [], ['access-token' => 'wrong_access_token']);

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');
    }

    /**
     * @covers ::create
     */
    public function testCreateWithoutAccessToken()
    {
        $response = $this->json('PUT', '/api/inventory/create', []);

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');
    }


    /**
     * @covers ::exists
     * @dataProvider inventoryDataProvider
     * 
     * @param array $inventoryParams
     */
    public function testExists(array $inventoryParams)
    {
        $seeder = new InventorySeeder();
        $seeder->seed();

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['manufacturer'] = $seeder->inventoryMfg->name;
        $inventoryParams['brand'] = $seeder->brand->name;
        $inventoryParams['category'] = $seeder->category->legacy_category;

        // Insert Into DB
        $inventory = factory(Inventory::class)->create($inventoryParams);
        $this->assertDatabaseHas('inventory', ['inventory_id' => $inventory->inventory_id]);


        // Get Exists Params
        $existsParams = [
            'dealer_id' => $inventoryParams['dealer_id'],
            'stock' => $inventoryParams['stock']
        ];
        $response = $this->json('GET', '/api/inventory/exists', $existsParams, ['access-token' => $seeder->authToken->access_token]);

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
     * @dataProvider inventoryDataProvider
     * 
     * @param array $inventoryParams
     */
    public function testExistsFalse(array $inventoryParams)
    {
        $seeder = new InventorySeeder();
        $seeder->seed();

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['manufacturer'] = $seeder->inventoryMfg->name;
        $inventoryParams['brand'] = $seeder->brand->name;
        $inventoryParams['category'] = $seeder->category->legacy_category;

        // Insert Into DB
        $inventory = factory(Inventory::class)->create($inventoryParams);
        $this->assertDatabaseHas('inventory', ['inventory_id' => $inventory->inventory_id]);


        // Get Exists Params
        $existsParams = [
            'dealer_id' => $inventoryParams['dealer_id'],
            'stock' => $inventoryParams['stock'] . '_unused'
        ];
        $response = $this->json('GET', '/api/inventory/exists', $existsParams, ['access-token' => $seeder->authToken->access_token]);

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
        $inventoryParams['manufacturer'] = $seeder->inventoryMfg->name;
        $inventoryParams['brand'] = $seeder->brand->name;
        $inventoryParams['category'] = $seeder->category->legacy_category;

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
            'stock' => $inventoryParams['stock']
        ];
        $response = $this->json('GET', '/api/inventory/exists', $existsParams, ['access-token' => $seeder->authToken->access_token]);

        $response->assertStatus(200);

        $existsParams['inventory_id'] = $inventory2->inventory_id;
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
     * @dataProvider inventoryDataProvider
     * 
     * @param array $inventoryParams
     */
    public function testExistsWithInventoryFalse(array $inventoryParams)
    {
        $seeder = new InventorySeeder();
        $seeder->seed();

        $inventoryParams['dealer_id'] = $seeder->dealer->dealer_id;
        $inventoryParams['dealer_location_id'] = $seeder->dealerLocation->dealer_location_id;
        $inventoryParams['manufacturer'] = $seeder->inventoryMfg->name;
        $inventoryParams['brand'] = $seeder->brand->name;
        $inventoryParams['category'] = $seeder->category->legacy_category;

        // Insert Into DB
        $inventory = factory(Inventory::class)->create($inventoryParams);
        $this->assertDatabaseHas('inventory', ['inventory_id' => $inventory->inventory_id]);


        // Get Exists Params
        $existsParams = [
            'dealer_id' => $inventoryParams['dealer_id'],
            'inventory_id' => $inventory->inventory_id,
            'stock' => $inventoryParams['stock'] . '_unused'
        ];
        $response = $this->json('GET', '/api/inventory/exists', $existsParams, ['access-token' => $seeder->authToken->access_token]);

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
     * Examples of invalid query parameters with their respective expected exception class name and its messages
     *
     * @return array[]
     */
    public function invalidQueryParametersProvider(): array
    {
        return [                                // array $parameters, int $expectedTotal, int $expectedLastPage, string $expectedCustomerName
            'InventoryId must be an integer' => [[], TypeError::class, 'Argument 1 passed to App\Http\Controllers\v1\Inventory\InventoryController::history() must be of the type int, null given', null],
            'Customer must to be an integer' => [['inventory_id' => 666999, 'customer_id' => [666999]], ResourceException::class, 'Validation Failed', 'The customer id needs to be an integer.'],
            'Search term invalid'            => [['inventory_id' => 666999, 'search_term' => ['Truck']], ResourceException::class, 'Validation Failed', 'The search term must be a string.'],
            'Sort invalid'                   => [['inventory_id' => 666999, 'sort' => '-with'], ResourceException::class, 'Validation Failed', 'The selected sort is invalid.'],
            'Per page invalid (min)'         => [['inventory_id' => 666999, 'per_page' => -10], ResourceException::class, 'Validation Failed', 'The per page must be at least 1.'],
            'Per page invalid (max)'         => [['inventory_id' => 666999, 'per_page' => 5000000], ResourceException::class, 'Validation Failed', 'The per page may not be greater than 2000.']
        ];
    }

    /**
     * @return int[]
     */
    public function inventoryDataProvider(): array
    {
        return [[
            [
                'entity_type_id' => 1,
                'active' => true,
                'title' => 'test_title',
                'stock' => 'test_stock',
                'model' => 'test_model',
                'qb_item_category_id' => 111,
                'description' => 'test_description',
                'description_html' => 'test_description_html',
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
                'cost_of_unit' => 'test_cost_of_unit',
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
            ]
        ]];
    }
}
