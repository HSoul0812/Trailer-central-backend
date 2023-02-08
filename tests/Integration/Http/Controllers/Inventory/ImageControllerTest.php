<?php

namespace Tests\Integration\Http\Controllers\Inventory;

use Tests\database\seeds\Inventory\InventoryImageSeeder;
use Tests\Integration\IntegrationTestCase;

/**
 * Class ImageControllerTest
 * @package Tests\Integration\Http\Controllers\Inventory
 *
 * @coversDefaultClass \App\Http\Controllers\v1\Inventory\ImageController
 */
class ImageControllerTest extends IntegrationTestCase
{
    /**
     * @covers ::create
     * @dataProvider createDataProvider
     */
    public function testCreate(array $params)
    {
        $seeder = new InventoryImageSeeder();
        $seeder->seed();

        $queryParams = array_merge($params, ['url' => $seeder->localImageUrl]);
        $headers = ['access-token' => $seeder->authToken->access_token];

        $response = $this->json('PUT', "/api/inventory/{$seeder->inventory->inventory_id}/images", $queryParams, $headers);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseJson);
        $this->assertNotEmpty($responseJson['data']);

        $this->assertArrayHasKey('url', $responseJson['data']);
        $this->assertNotFalse(filter_var($responseJson['data']['url'], FILTER_VALIDATE_URL));
        $this->assertArrayHasKey('image_id', $responseJson['data']);
        $this->assertIsInt($responseJson['data']['image_id']);

        foreach ($params as $key => $value) {
            $this->assertArrayHasKey($key, $responseJson['data']);
            $this->assertSame($responseJson['data'][$key], $value);
        }

        $this->assertDatabaseHas('image', [
            'image_id' => $responseJson['data']['image_id']
        ]);

        $this->assertDatabaseHas('inventory_image', [
            'image_id' => $responseJson['data']['image_id'],
            'inventory_id' => $seeder->inventory->inventory_id
        ]);

        $seeder->cleanUp();
    }

    /**
     * @covers ::create
     */
    public function testCreateWrongAccessToken()
    {
        $seeder = new InventoryImageSeeder();
        $seeder->seed();

        $queryParams = ['url' => $seeder->localImageUrl];

        $response = $this->json('PUT', "/api/inventory/{$seeder->inventory->inventory_id}/images", $queryParams);

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');

        $seeder->cleanUp();
    }

    /**
     * @covers ::create
     */
    public function testCreateWrongInventoryId()
    {
        $inventoryId = PHP_INT_MAX;

        $seeder = new InventoryImageSeeder();
        $seeder->seed();

        $queryParams = ['url' => $seeder->localImageUrl];
        $headers = ['access-token' => $seeder->authToken->access_token];

        $response = $this->json('PUT', "/api/inventory/{$inventoryId}/images", $queryParams, $headers);

        $response->assertStatus(422);

        $seeder->cleanUp();
    }

    /**
     * @covers ::create
     */
    public function testCreateWithoutUrl()
    {
        $seeder = new InventoryImageSeeder();
        $seeder->seed();

        $headers = ['access-token' => $seeder->authToken->access_token];

        $response = $this->json('PUT', "/api/inventory/{$seeder->inventory->inventory_id}/images", [], $headers);

        $response->assertStatus(422);

        $seeder->cleanUp();
    }

    /**
     * @covers ::bulkDestroy
     */
    public function testBulkDestroy()
    {
        $seeder1 = new InventoryImageSeeder(['numberOfImages' => 3]);
        $seeder1->seed();

        $seeder2 = new InventoryImageSeeder(['numberOfImages' => 5]);
        $seeder2->seed();

        foreach ($seeder1->images as $image) {
            $this->assertDatabaseHas('image', [
                'image_id' => $image->image_id
            ]);

            $this->assertDatabaseHas('inventory_image', [
                'image_id' => $image->image_id,
                'inventory_id' => $seeder1->inventory->inventory_id
            ]);
        }

        foreach ($seeder2->images as $image) {
            $this->assertDatabaseHas('image', [
                'image_id' => $image->image_id
            ]);

            $this->assertDatabaseHas('inventory_image', [
                'image_id' => $image->image_id,
                'inventory_id' => $seeder2->inventory->inventory_id
            ]);
        }

        $response = $this->json('DELETE', "/api/inventory/{$seeder1->inventory->inventory_id}/images", [], [
            'access-token' => $seeder1->authToken->access_token
        ]);

        $response->assertStatus(204);

        foreach ($seeder1->images as $image) {
            $this->assertDatabaseMissing('image', [
                'image_id' => $image->image_id
            ]);

            $this->assertDatabaseMissing('inventory_image', [
                'image_id' => $image->image_id,
                'inventory_id' => $seeder1->inventory->inventory_id
            ]);
        }

        foreach ($seeder2->images as $image) {
            $this->assertDatabaseHas('image', [
                'image_id' => $image->image_id
            ]);

            $this->assertDatabaseHas('inventory_image', [
                'image_id' => $image->image_id,
                'inventory_id' => $seeder2->inventory->inventory_id
            ]);
        }

        $response = $this->json('DELETE', "/api/inventory/{$seeder2->inventory->inventory_id}/images", [], [
            'access-token' => $seeder2->authToken->access_token
        ]);

        $response->assertStatus(204);

        foreach ($seeder2->images as $image) {
            $this->assertDatabaseMissing('image', [
                'image_id' => $image->image_id
            ]);

            $this->assertDatabaseMissing('inventory_image', [
                'image_id' => $image->image_id,
                'inventory_id' => $seeder2->inventory->inventory_id
            ]);
        }

        $seeder1->cleanUp();
        $seeder2->cleanUp();
    }

    /**
     * @covers ::bulkDestroy
     */
    public function testBulkDestroyWithImageIds()
    {
        $seeder = new InventoryImageSeeder(['numberOfImages' => 3]);
        $seeder->seed();

        foreach ($seeder->images as $image) {
            $this->assertDatabaseHas('image', [
                'image_id' => $image->image_id
            ]);

            $this->assertDatabaseHas('inventory_image', [
                'image_id' => $image->image_id,
                'inventory_id' => $seeder->inventory->inventory_id
            ]);
        }

        $response = $this->json('DELETE', "/api/inventory/{$seeder->inventory->inventory_id}/images", [
            'image_ids' => [$seeder->images[0]->image_id]
        ], [
            'access-token' => $seeder->authToken->access_token
        ]);

        $response->assertStatus(204);

        foreach ($seeder->images as $key => $image) {
            if ($key === 0) {
                $this->assertDatabaseMissing('image', [
                    'image_id' => $image->image_id
                ]);

                $this->assertDatabaseMissing('inventory_image', [
                    'image_id' => $image->image_id,
                    'inventory_id' => $seeder->inventory->inventory_id
                ]);
            } else {
                $this->assertDatabaseHas('image', [
                    'image_id' => $image->image_id
                ]);

                $this->assertDatabaseHas('inventory_image', [
                    'image_id' => $image->image_id,
                    'inventory_id' => $seeder->inventory->inventory_id
                ]);
            }
        }

        $seeder->cleanUp();
    }

    /**
     * @covers ::bulkDestroy
     */
    public function testBulkDestroyWrongAccessToken()
    {
        $seeder = new InventoryImageSeeder();
        $seeder->seed();

        $response = $this->json('DELETE', "/api/inventory/{$seeder->inventory->inventory_id}/images");

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');

        $seeder->cleanUp();
    }

    /**
     * @covers ::bulkDestroy
     */
    public function testBulkDestroyWrongInventoryId()
    {
        $seeder = new InventoryImageSeeder();
        $seeder->seed();

        $inventoryId = PHP_INT_MAX;

        $response = $this->json('DELETE', "/api/inventory/{$inventoryId}/images", [], [
            'access-token' => $seeder->authToken->access_token
        ]);

        $response->assertStatus(422);

        $seeder->cleanUp();
    }

    /**
     * @return array[]
     */
    public function createDataProvider(): array
    {
        return [
            [[]],
            [['position' => 50, 'is_secondary' => 1]],
            [['position' => 20, 'is_secondary' => 0]],
        ];
    }
}
