<?php

namespace Tests\Integration\Http\Controllers\User;

use App\Models\User\DealerLogo;
use App\Services\User\DealerLogoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_DEALER
 * @group DW_DEALER_LOGO
 */
class DealerLogoControllerTest extends TestCase
{
    private $dealerId;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerId = TestCase::getTestDealerId();
    }

    public function test_it_can_create_a_dealer_logo()
    {
        Storage::fake(DealerLogoService::STORAGE_DISK);

        $statement = 'Hello World';
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->post('/api/user/logo', [
                'logo' => UploadedFile::fake()->create('image.png', 1024, 'image/png'),
                'benefit_statement' => $statement
            ]);
        $response->assertStatus(Response::HTTP_OK);

        Storage::disk(DealerLogoService::STORAGE_DISK)->assertExists("dealer_logos/{$this->dealerId}_logo.png");

        $this->assertDatabaseHas(DealerLogo::getTableName(), [
            'dealer_id' => $this->dealerId,
            'benefit_statement' => $statement
        ]);

        DealerLogo::whereId($response->json('data.id'))->delete();
    }

    public function test_it_validates_the_create_logo_request()
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->post('/api/user/logo', [
                'logo' => UploadedFile::fake()->create('image.pdf', 1024, 'application/pdf'),
                'benefit_statement' => null
            ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('logo');

        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->post('/api/user/logo', [
                'logo' => UploadedFile::fake()->create('image.png', 7085, 'image/png'),
                'benefit_statement' => null
            ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('logo');
    }

    public function test_it_can_update_a_logo()
    {
        Storage::fake(DealerLogoService::STORAGE_DISK);

        $statement = 'Hello World';
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->post('/api/user/logo', [
                'logo' => UploadedFile::fake()->create('image.png', 1024, 'image/png'),
                'benefit_statement' => $statement
            ]);
        $response->assertStatus(Response::HTTP_OK);

        Storage::disk(DealerLogoService::STORAGE_DISK)->assertExists("dealer_logos/{$this->dealerId}_logo.png");

        $id = $response->json('data.id');

        $newStatement = 'Bye World';

        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->post('/api/user/logo', [
                'logo' => null,
                'benefit_statement' => $newStatement
            ]);
        $response->assertStatus(Response::HTTP_OK);

        Storage::disk(DealerLogoService::STORAGE_DISK)->assertMissing("dealer_logos/{$this->dealerId}_logo.png");

        $this->assertDatabaseHas(DealerLogo::getTableName(), [
            'dealer_id' => $this->dealerId,
            'filename' => null,
            'benefit_statement' => $newStatement
        ]);
        DealerLogo::whereId($id)->delete();
    }
}
