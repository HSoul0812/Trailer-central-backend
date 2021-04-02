<?php


namespace Tests\Integration\Http\Controllers\Feed\Factory;

use Tests\TestCase;

/**
 * Class ShowroomControllerTest
 * @package Tests\Integration\Http\Controllers\Feed\Factory
 *
 * @coversDefaultClass \App\Http\Controllers\v1\Feed\Factory\ShowroomController
 */
class ShowroomControllerTest extends TestCase
{
    /**
     * @covers ::index
     */
    public function testIndex()
    {
        $response = $this->json('GET', '/api/feed/factory/showroom');

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertNotEmpty($responseJson);
        $this->assertIsArray($responseJson);

        $this->assertArrayHasKey('data', $responseJson);
        $this->assertNotEmpty($responseJson['data']);
        $this->assertIsArray($responseJson['data']);

        $this->assertArrayHasKey('real_model', $responseJson['data'][0]);
        $this->assertArrayHasKey('images', $responseJson['data'][0]);
        $this->assertArrayHasKey('inventoryCategory', $responseJson['data'][0]);
        $this->assertArrayHasKey('model', $responseJson['data'][0]);
        $this->assertArrayHasKey('real_model', $responseJson['data'][0]);
        $this->assertArrayHasKey('category', $responseJson['data'][0]);
        $this->assertArrayHasKey('description', $responseJson['data'][0]);
        $this->assertArrayHasKey('description_txt', $responseJson['data'][0]);
        $this->assertArrayHasKey('brand', $responseJson['data'][0]);
        $this->assertArrayHasKey('year', $responseJson['data'][0]);

        $this->assertArrayHasKey('meta', $responseJson);
        $this->assertNotEmpty($responseJson['meta']);
        $this->assertIsArray($responseJson['meta']);

        $this->assertArrayHasKey('pagination', $responseJson['meta']);
        $this->assertNotEmpty($responseJson['meta']['pagination']);
        $this->assertIsArray($responseJson['meta']['pagination']);

        $this->assertArrayHasKey('total', $responseJson['meta']['pagination']);
        $this->assertArrayHasKey('count', $responseJson['meta']['pagination']);
        $this->assertArrayHasKey('per_page', $responseJson['meta']['pagination']);
        $this->assertArrayHasKey('current_page', $responseJson['meta']['pagination']);
        $this->assertArrayHasKey('total_pages', $responseJson['meta']['pagination']);
    }

    /**
     * @covers ::index
     */
    public function testIndexWithSelect()
    {
        $response = $this->json('GET', '/api/feed/factory/showroom', ['select' => ['model', 'id']]);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertNotEmpty($responseJson);
        $this->assertIsArray($responseJson);

        $this->assertArrayHasKey('data', $responseJson);
        $this->assertNotEmpty($responseJson['data']);
        $this->assertIsArray($responseJson['data']);

        $this->assertArrayHasKey('real_model', $responseJson['data'][0]);
        $this->assertArrayHasKey('id', $responseJson['data'][0]);
        $this->assertCount(3, array_keys($responseJson['data'][0]));

        $this->assertArrayHasKey('meta', $responseJson);
        $this->assertNotEmpty($responseJson['meta']);
        $this->assertIsArray($responseJson['meta']);

        $this->assertArrayHasKey('pagination', $responseJson['meta']);
        $this->assertNotEmpty($responseJson['meta']['pagination']);
        $this->assertIsArray($responseJson['meta']['pagination']);

        $this->assertArrayHasKey('total', $responseJson['meta']['pagination']);
        $this->assertArrayHasKey('count', $responseJson['meta']['pagination']);
        $this->assertArrayHasKey('per_page', $responseJson['meta']['pagination']);
        $this->assertArrayHasKey('current_page', $responseJson['meta']['pagination']);
        $this->assertArrayHasKey('total_pages', $responseJson['meta']['pagination']);
    }
}
