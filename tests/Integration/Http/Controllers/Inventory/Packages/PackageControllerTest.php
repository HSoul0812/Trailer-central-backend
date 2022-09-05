<?php

namespace Tests\Integration\Http\Controllers\Inventory\Packages;

use Tests\database\seeds\Inventory\InventorySeeder;
use Tests\database\seeds\Inventory\PackageSeeder;
use Tests\TestCase;

/**
 * Class PackageControllerTest
 * @package Tests\Integration\Http\Controllers\Inventory\Packages
 *
 * @coversDefaultClass \App\Http\Controllers\v1\Inventory\PackageController
 */
class PackageControllerTest extends TestCase
{
    /**
     * @covers ::index
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testIndex()
    {
        $inventorySeeder = new InventorySeeder(['withInventory' => true]);
        $inventorySeeder->seed();

        $packageSeeder = new PackageSeeder([
            'dealer_id' => $inventorySeeder->dealer->dealer_id,
            'inventory_id' => $inventorySeeder->inventory->inventory_id
        ]);
        $packageSeeder->seed();

        $response = $this->json('GET', '/api/inventory/packages', [], ['access-token' => $inventorySeeder->authToken->access_token]);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseJson);
        $this->assertCount(1, $responseJson['data']);

        $this->assertArrayHasKey('id', $responseJson['data'][0]);
        $this->assertSame($packageSeeder->package->id, $responseJson['data'][0]['id']);
        $this->assertArrayHasKey('visible_with_main_item', $responseJson['data'][0]);
        $this->assertEquals($packageSeeder->package->visible_with_main_item, $responseJson['data'][0]['visible_with_main_item']);

        $this->assertArrayHasKey('inventories', $responseJson['data'][0]);
        $this->assertIsArray($responseJson['data'][0]['inventories']);
        $this->assertCount(1, $responseJson['data'][0]['inventories']);
        $this->assertArrayHasKey('inventory_id', $responseJson['data'][0]['inventories'][0]);
        $this->assertSame($inventorySeeder->inventory->inventory_id, $responseJson['data'][0]['inventories'][0]['inventory_id']);
        $this->assertArrayHasKey('is_main_item', $responseJson['data'][0]['inventories'][0]);
        $this->assertEquals($packageSeeder->packageInventory->is_main_item, $responseJson['data'][0]['inventories'][0]['is_main_item']);

        $packageSeeder->cleanUp();
        $inventorySeeder->cleanUp();
    }

    /**
     * @covers ::index
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testIndexWithoutAccessToken()
    {
        $response = $this->json('GET', '/api/inventory/packages');

        $response->assertStatus(403);
    }

    /**
     * @covers ::show
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testShow()
    {
        $inventorySeeder = new InventorySeeder(['withInventory' => true]);
        $inventorySeeder->seed();

        $packageSeeder = new PackageSeeder([
            'dealer_id' => $inventorySeeder->dealer->dealer_id,
            'inventory_id' => $inventorySeeder->inventory->inventory_id
        ]);
        $packageSeeder->seed();

        $response = $this->json('GET', '/api/inventory/packages/' . $packageSeeder->package->id, [], [
            'access-token' => $inventorySeeder->authToken->access_token
        ]);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseJson);

        $this->assertArrayHasKey('id', $responseJson['data']);
        $this->assertSame($packageSeeder->package->id, $responseJson['data']['id']);
        $this->assertArrayHasKey('visible_with_main_item', $responseJson['data']);
        $this->assertEquals($packageSeeder->package->visible_with_main_item, $responseJson['data']['visible_with_main_item']);

        $this->assertArrayHasKey('inventories', $responseJson['data']);
        $this->assertIsArray($responseJson['data']['inventories']);
        $this->assertCount(1, $responseJson['data']['inventories']);
        $this->assertArrayHasKey('inventory_id', $responseJson['data']['inventories'][0]);
        $this->assertSame($inventorySeeder->inventory->inventory_id, $responseJson['data']['inventories'][0]['inventory_id']);
        $this->assertArrayHasKey('is_main_item', $responseJson['data']['inventories'][0]);
        $this->assertEquals($packageSeeder->packageInventory->is_main_item, $responseJson['data']['inventories'][0]['is_main_item']);

        $packageSeeder->cleanUp();
        $inventorySeeder->cleanUp();
    }

    /**
     * @covers ::show
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testShowWithoutAccessToken()
    {
        $response = $this->json('GET', '/api/inventory/packages/1');

        $response->assertStatus(403);
    }

    /**
     * @covers ::create
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testCreate()
    {
        $inventorySeeder = new InventorySeeder(['withInventory' => true]);
        $inventorySeeder->seed();

        $expectedPackagesInventory = [
            'inventory_id' => $inventorySeeder->inventory->inventory_id,
            'is_main_item' => true
        ];

        $expectedPackage = [
            'dealer_id' => $inventorySeeder->dealer->dealer_id,
            'visible_with_main_item' => true,
        ];

        $packageParams = [
            'visible_with_main_item' => $expectedPackage['visible_with_main_item'],
            'inventories' => [
                $expectedPackagesInventory
            ]
        ];

        $this->assertDatabaseMissing('packages', ['dealer_id' => $expectedPackage['dealer_id']]);

        $response = $this->json('PUT', '/api/inventory/packages', $packageParams, ['access-token' => $inventorySeeder->authToken->access_token]);

        $response->assertStatus(201);

        $responseJson = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $responseJson);
        $this->assertArrayHasKey('status', $responseJson['response']);
        $this->assertSame('success', $responseJson['response']['status']);
        $this->assertArrayHasKey('data', $responseJson['response']);
        $this->assertArrayHasKey('id', $responseJson['response']['data']);
        $this->assertSame('success', $responseJson['response']['status']);

        $this->assertDatabaseHas('packages', $expectedPackage);
        $this->assertDatabaseHas('packages_inventory', $expectedPackagesInventory);

        $inventorySeeder->cleanUp();
    }

    /**
     * @covers ::create
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testCreateWithoutAccessToken()
    {
        $inventorySeeder = new InventorySeeder(['withInventory' => true]);
        $inventorySeeder->seed();

        $expectedPackagesInventory = [
            'inventory_id' => $inventorySeeder->inventory->inventory_id,
            'is_main_item' => true
        ];

        $expectedPackage = [
            'dealer_id' => $inventorySeeder->dealer->dealer_id,
            'visible_with_main_item' => true,
        ];

        $packageParams = [
            'visible_with_main_item' => $expectedPackage['visible_with_main_item'],
            'inventories' => [
                $expectedPackagesInventory
            ]
        ];

        $this->assertDatabaseMissing('packages', ['dealer_id' => $expectedPackage['dealer_id']]);

        $response = $this->json('PUT', '/api/inventory/packages', $packageParams);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('packages', $expectedPackage);
        $this->assertDatabaseMissing('packages_inventory', $expectedPackagesInventory);

        $inventorySeeder->cleanUp();
    }

    /**
     * @covers ::update
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testUpdate()
    {
        $inventorySeeder = new InventorySeeder(['withInventory' => true]);
        $inventorySeeder->seed();

        $packageSeeder = new PackageSeeder([
            'dealer_id' => $inventorySeeder->dealer->dealer_id,
            'inventory_id' => $inventorySeeder->inventory->inventory_id,
            'visible_with_main_item' => true,
            'is_main_item' => true
        ]);
        $packageSeeder->seed();

        $createdPackage = [
            'id' => $packageSeeder->package->id,
            'dealer_id' => $packageSeeder->package->dealer_id,
            'visible_with_main_item' => true,
        ];

        $createdPackagesInventory = [
            'package_id' => $packageSeeder->package->id,
            'inventory_id' => $inventorySeeder->inventory->inventory_id,
            'is_main_item' => true
        ];

        $this->assertDatabaseHas('packages', $createdPackage);
        $this->assertDatabaseHas('packages_inventory', $createdPackagesInventory);

        $inventorySeeder2 = new InventorySeeder(['withInventory' => true]);
        $inventorySeeder2->seed();

        $expectedPackage = [
            'id' => $packageSeeder->package->id,
            'dealer_id' => $packageSeeder->package->dealer_id,
            'visible_with_main_item' => false,
        ];

        $expectedPackagesInventory = [
            'package_id' => $packageSeeder->package->id,
            'inventory_id' => $inventorySeeder2->inventory->inventory_id,
            'is_main_item' => false
        ];

        $packageParams = [
            'visible_with_main_item' => false,
            'inventories' => [
                $expectedPackagesInventory
            ]
        ];

        $response = $this->json('POST', '/api/inventory/packages/' . $packageSeeder->package->id, $packageParams, ['access-token' => $inventorySeeder->authToken->access_token]);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $responseJson);
        $this->assertArrayHasKey('status', $responseJson['response']);
        $this->assertSame('success', $responseJson['response']['status']);
        $this->assertArrayHasKey('data', $responseJson['response']);
        $this->assertArrayHasKey('id', $responseJson['response']['data']);
        $this->assertSame('success', $responseJson['response']['status']);

        $this->assertDatabaseHas('packages', $expectedPackage);
        $this->assertDatabaseHas('packages_inventory', $expectedPackagesInventory);

        $packageSeeder->cleanUp();
        $inventorySeeder2->cleanUp();
        $inventorySeeder->cleanUp();
    }

    /**
     * @covers ::update
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testUpdateWithoutAccessToken()
    {
        $response = $this->json('POST', '/api/inventory/packages/1');

        $response->assertStatus(403);
    }

    /**
     * @covers ::destroy
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testDestroy()
    {
        $inventorySeeder = new InventorySeeder(['withInventory' => true]);
        $inventorySeeder->seed();

        $packageSeeder = new PackageSeeder([
            'dealer_id' => $inventorySeeder->dealer->dealer_id,
            'inventory_id' => $inventorySeeder->inventory->inventory_id,
            'visible_with_main_item' => true,
            'is_main_item' => true
        ]);
        $packageSeeder->seed();

        $createdPackage = [
            'id' => $packageSeeder->package->id,
            'dealer_id' => $packageSeeder->package->dealer_id,
            'visible_with_main_item' => true,
        ];

        $createdPackagesInventory = [
            'package_id' => $packageSeeder->package->id,
            'inventory_id' => $inventorySeeder->inventory->inventory_id,
            'is_main_item' => true
        ];

        $this->assertDatabaseHas('packages', $createdPackage);
        $this->assertDatabaseHas('packages_inventory', $createdPackagesInventory);

        $response = $this->json('DELETE', '/api/inventory/packages/' . $packageSeeder->package->id, [], ['access-token' => $inventorySeeder->authToken->access_token]);

        $response->assertStatus(204);

        $responseJson = json_decode($response->getContent(), true);
        $this->assertEmpty($responseJson);

        $this->assertDatabaseMissing('packages', $createdPackage);
        $this->assertDatabaseMissing('packages_inventory', $createdPackagesInventory);

        $packageSeeder->cleanUp();
        $inventorySeeder->cleanUp();
    }
}
