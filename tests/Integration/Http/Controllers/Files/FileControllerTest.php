<?php

namespace Tests\Integration\Http\Controllers\Files;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\database\seeds\Files\FileSeeder;
use Tests\TestCase;

/**
 * Class FileControllerTest
 * @package Tests\Integration\Http\Controllers\Files
 *
 * @coversDefaultClass \App\Http\Controllers\v1\File\FileController
 */
class FileControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('local_tmp');
    }

    public function tearDown(): void
    {
        Storage::fake('local_tmp');

        parent::tearDown();
    }

    /**
     * @covers ::uploadLocal
     */
    public function testUploadLocal()
    {
        $seeder = new FileSeeder();
        $params = ['file' => UploadedFile::fake()->create('test.pdf', '1000', 'application/pdf')];

        $seeder->seed();

        $response = $this->json('POST', '/api/files/local', $params, ['access-token' => $seeder->authToken->access_token]);

        $response->assertStatus(200);

        $content = json_decode($response->getContent(), true);

        $this->assertNotEmpty($content['data']['url']);

        Storage::disk('local_tmp')->assertExists(str_replace('/storage', '', $content['data']['url']));

        $seeder->cleanUp();
    }

    /**
     * @covers ::uploadLocal
     */
    public function testUploadLocalWrongFile()
    {
        $seeder = new FileSeeder();
        $params = ['file' => 'wrong_file'];

        $seeder->seed();

        $response = $this->json('POST', '/api/files/local', $params, ['access-token' => $seeder->authToken->access_token]);

        $response->assertStatus(422);

        $seeder->cleanUp();
    }

    /**
     * @covers ::uploadLocal
     */
    public function testUploadLocalWrongAccessToken()
    {
        $response = $this->json('POST', '/api/files/local', [], ['access-token' => 'wrong_access_token']);

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');
    }
}
