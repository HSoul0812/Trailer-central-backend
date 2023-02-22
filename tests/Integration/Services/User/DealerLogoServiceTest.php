<?php

namespace Tests\Integration\Services\User;

use App\Services\User\DealerLogoServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_DEALER
 * @group DW_DEALER_LOGO
 */
class DealerLogoServiceTest extends TestCase
{
    /**
     * @var DealerLogoServiceInterface
     */
    private $dealerLogoService;

    private $dealerId;

    private $uploadedFilename;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerLogoService = $this->app->make(DealerLogoServiceInterface::class);
        $this->dealerId = $this->getTestDealerId();
        $this->uploadedFilename = "dealer_logos/{$this->dealerId}_logo.png";
    }

    public function test_it_can_upload_a_logo()
    {
        Storage::fake('s3');

        $file = $this->dealerLogoService->upload(
            $this->dealerId,
            UploadedFile::fake()->create('image.png', 1024, 'image/png')
        );

        $this->assertSame($this->uploadedFilename, $file);
        Storage::disk('s3')->assertExists($this->uploadedFilename);
    }
}
