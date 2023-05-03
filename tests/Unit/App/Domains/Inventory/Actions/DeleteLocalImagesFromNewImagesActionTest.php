<?php

namespace Tests\Unit\App\Domains\Inventory\Actions;

use App\Domains\Inventory\Actions\DeleteLocalImagesFromNewImagesAction;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageService;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\Common\TestCase;

class DeleteLocalImagesFromNewImagesActionTest extends TestCase
{
    public function testItWontProcessEmptyCollection()
    {
        $deletedImagesUrls = resolve(DeleteLocalImagesFromNewImagesAction::class)->execute(collect([]));

        $this->assertEmpty($deletedImagesUrls);
    }

    public function testItCanDeleteImagesFromNewImagesCollection()
    {
        Storage::fake(ImageService::LOCAL_IMAGES_DISK);

        $storage = Storage::disk(ImageService::LOCAL_IMAGES_DISK);

        $directory = ImageService::LOCAL_IMAGES_DIRECTORY;

        $storage->putFileAs($directory, UploadedFile::fake()->image('image1.jpg'), 'image1.jpg');
        $storage->putFileAs($directory, UploadedFile::fake()->image('image2.jpg'), 'image2.jpg');
        $storage->putFileAs($directory, UploadedFile::fake()->image('image3.jpg'), 'image3.jpg');

        $storage->assertExists("$directory/image1.jpg");
        $storage->assertExists("$directory/image2.jpg");
        $storage->assertExists("$directory/image3.jpg");

        // Intentionally delete only 2 images
        $newImages = collect([[
            'url' => config('app.url') . '/upload/tmp/images/image1.jpg',
        ], [
            'url' => config('app.url') . '/upload/tmp/images/image2.jpg',
        ], [
            // This one is on a different domain name, so it should be totally ignored
            'url' => 'https://google.com/upload/tmp/images/image2.jpg',
        ], [
            // This one has no url, so it should be totally ignored
        ]]);

        $deletedImagesUrls = resolve(DeleteLocalImagesFromNewImagesAction::class)->execute($newImages);

        $storage->assertMissing("$directory/image1.jpg");
        $storage->assertMissing("$directory/image2.jpg");
        $storage->assertExists("$directory/image3.jpg");

        $this->assertCount(2, $deletedImagesUrls);
        $this->assertStringContainsString('image1.jpg', $deletedImagesUrls->get(0));
        $this->assertStringContainsString('image2.jpg', $deletedImagesUrls->get(1));
    }
}
