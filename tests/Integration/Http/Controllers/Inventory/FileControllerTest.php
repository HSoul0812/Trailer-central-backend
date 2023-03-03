<?php

namespace Tests\Integration\Http\Controllers\Inventory;

use Tests\database\seeds\Inventory\InventoryFileSeeder;
use Tests\Integration\IntegrationTestCase;

/**
 * Class FileControllerTest
 * @package Tests\Integration\Http\Controllers\Inventory
 *
 * @coversDefaultClass \App\Http\Controllers\v1\Inventory\FileController
 */
class FileControllerTest extends IntegrationTestCase
{
    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $seeder = new InventoryFileSeeder();
        $seeder->seed();

        $params = [
            'url' => $seeder->localFileUrl,
            'title' => InventoryFileSeeder::FILENAME
        ];

        $headers = ['access-token' => $seeder->authToken->access_token];

        $response = $this->json('PUT', "/api/inventory/{$seeder->inventory->inventory_id}/files", $params, $headers);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseJson);
        $this->assertNotEmpty($responseJson['data']);

        $this->assertArrayHasKey('url', $responseJson['data']);
        $this->assertNotFalse(filter_var($responseJson['data']['url'], FILTER_VALIDATE_URL));
        $this->assertArrayHasKey('file_id', $responseJson['data']);
        $this->assertIsInt($responseJson['data']['file_id']);
        $this->assertArrayHasKey('title', $responseJson['data']);
        $this->assertSame($responseJson['data']['title'], InventoryFileSeeder::FILENAME);
        $this->assertArrayHasKey('mime_type', $responseJson['data']);
        $this->assertSame($responseJson['data']['mime_type'], 'text/plain');
        $this->assertArrayHasKey('type', $responseJson['data']);
        $this->assertSame($responseJson['data']['type'], 'text/plain');

        $this->assertDatabaseHas('file', [
            'id' => $responseJson['data']['file_id']
        ]);

        $this->assertDatabaseHas('inventory_file', [
            'file_id' => $responseJson['data']['file_id'],
            'inventory_id' => $seeder->inventory->inventory_id
        ]);

        $seeder->cleanUp();
    }

    /**
     * @covers ::create
     */
    public function testCreateWrongAccessToken()
    {
        $seeder = new InventoryFileSeeder();
        $seeder->seed();

        $params = [
            'url' => $seeder->localFileUrl,
            'title' => InventoryFileSeeder::FILENAME
        ];

        $response = $this->json('PUT', "/api/inventory/{$seeder->inventory->inventory_id}/files", $params);

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
        $seeder = new InventoryFileSeeder();
        $seeder->seed();

        $params = [
            'url' => $seeder->localFileUrl,
            'title' => InventoryFileSeeder::FILENAME
        ];

        $headers = ['access-token' => $seeder->authToken->access_token];

        $inventoryId = PHP_INT_MAX;

        $response = $this->json('PUT', "/api/inventory/{$inventoryId}/files", $params, $headers);

        $response->assertStatus(422);

        $seeder->cleanUp();
    }

    /**
     * @covers ::create
     * @dataProvider invalidCreateDataProvider
     */
    public function testCreateWithoutParams(array $params)
    {
        $seeder = new InventoryFileSeeder();
        $seeder->seed();

        $headers = ['access-token' => $seeder->authToken->access_token];

        $response = $this->json('PUT', "/api/inventory/{$seeder->inventory->inventory_id}/files", $params, $headers);

        $response->assertStatus(422);

        $seeder->cleanUp();
    }

    /**
     * @covers ::bulkDestroy
     */
    public function testBulkDestroy()
    {
        $seeder1 = new InventoryFileSeeder(['numberOfFiles' => 3]);
        $seeder1->seed();

        $seeder2 = new InventoryFileSeeder(['numberOfFiles' => 5]);
        $seeder2->seed();

        foreach ($seeder1->files as $file) {
            $this->assertDatabaseHas('file', [
                'id' => $file->id
            ]);

            $this->assertDatabaseHas('inventory_file', [
                'file_id' => $file->id,
                'inventory_id' => $seeder1->inventory->inventory_id
            ]);
        }

        foreach ($seeder2->files as $file) {
            $this->assertDatabaseHas('file', [
                'id' => $file->id
            ]);

            $this->assertDatabaseHas('inventory_file', [
                'file_id' => $file->id,
                'inventory_id' => $seeder2->inventory->inventory_id
            ]);
        }

        $response = $this->json('DELETE', "/api/inventory/{$seeder1->inventory->inventory_id}/files", [], [
            'access-token' => $seeder1->authToken->access_token
        ]);

        $response->assertStatus(204);

        foreach ($seeder1->files as $file) {
            $this->assertDatabaseMissing('file', [
                'id' => $file->id
            ]);

            $this->assertDatabaseMissing('inventory_file', [
                'file_id' => $file->id,
                'inventory_id' => $seeder1->inventory->inventory_id
            ]);
        }

        foreach ($seeder2->files as $file) {
            $this->assertDatabaseHas('file', [
                'id' => $file->id
            ]);

            $this->assertDatabaseHas('inventory_file', [
                'file_id' => $file->id,
                'inventory_id' => $seeder2->inventory->inventory_id
            ]);
        }

        $response = $this->json('DELETE', "/api/inventory/{$seeder2->inventory->inventory_id}/files", [], [
            'access-token' => $seeder2->authToken->access_token
        ]);

        $response->assertStatus(204);

        foreach ($seeder2->files as $file) {
            $this->assertDatabaseMissing('file', [
                'id' => $file->id
            ]);

            $this->assertDatabaseMissing('inventory_file', [
                'file_id' => $file->id,
                'inventory_id' => $seeder2->inventory->inventory_id
            ]);
        }

        $seeder1->cleanUp();
        $seeder2->cleanUp();
    }

    /**
     * @covers ::bulkDestroy
     */
    public function testBulkDestroyWrongAccessToken()
    {
        $seeder = new InventoryFileSeeder();
        $seeder->seed();

        $response = $this->json('DELETE', "/api/inventory/{$seeder->inventory->inventory_id}/files");

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
        $seeder = new InventoryFileSeeder();
        $seeder->seed();

        $inventoryId = PHP_INT_MAX;

        $response = $this->json('DELETE', "/api/inventory/{$inventoryId}/files", [], [
            'access-token' => $seeder->authToken->access_token
        ]);

        $response->assertStatus(422);

        $seeder->cleanUp();
    }

    /**
     * @return array[]
     */
    public function invalidCreateDataProvider(): array
    {
        return [
            ['Without Url' => ['title' => 'some_title']],
            ['Without Title' => ['url' => 'some_url']],
        ];
    }
}
