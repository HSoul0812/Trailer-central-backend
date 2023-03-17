<?php

namespace Tests\Integration\Services\User;

use App\Models\User\DealerLogo;
use App\Services\User\DealerLogoService;
use App\Services\User\DealerLogoServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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
        $now = now();
        Carbon::setTestNow($now);

        $this->uploadedFilename = "dealer_logos/{$this->dealerId}_{$now->getTimestamp()}_logo.png";
    }

    public function test_it_can_upload_a_logo()
    {
        Storage::fake(DealerLogoService::STORAGE_DISK);

        $file = $this->dealerLogoService->upload(
            $this->dealerId,
            UploadedFile::fake()->create('image.png', 1024, 'image/png')
        );

        $this->assertSame($this->uploadedFilename, $file);
        Storage::disk(DealerLogoService::STORAGE_DISK)->assertExists($this->uploadedFilename);
    }

    public function test_it_can_delete_a_logo()
    {
        Storage::fake(DealerLogoService::STORAGE_DISK);

        $logo = factory(DealerLogo::class)->create([
            'dealer_id' => $this->dealerId,
            'filename' => $this->uploadedFilename
        ]);

        $this->dealerLogoService->upload(
            $this->dealerId,
            UploadedFile::fake()->create('image.png', 1024, 'image/png')
        );

        Storage::disk(DealerLogoService::STORAGE_DISK)->assertExists($this->uploadedFilename);

        $this->dealerLogoService->delete($this->dealerId);

        Storage::disk(DealerLogoService::STORAGE_DISK)->assertMissing($this->uploadedFilename);

        $logo->delete();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        
        Carbon::setTestNow();
    }
}
