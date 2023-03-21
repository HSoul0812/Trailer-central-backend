<?php

namespace Tests\Unit\App\Domains\Images;

use App\Domains\Images\Actions\DeleteOldLocalImagesAction;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageService;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\Common\TestCase;

class DeleteOldLocalImagesActionTest extends TestCase
{
    public function testItCanDeleteOldImages()
    {
        Storage::fake(ImageService::LOCAL_IMAGES_DISK);

        $storage = Storage::disk(ImageService::LOCAL_IMAGES_DISK);

        $directory = ImageService::LOCAL_IMAGES_DIRECTORY;

        $storage->putFileAs($directory, UploadedFile::fake()->image('image1.jpg'), 'image1.jpg');
        $storage->putFileAs($directory, UploadedFile::fake()->image('image2.jpg'), 'image2.jpg');
        $storage->putFileAs($directory, UploadedFile::fake()->image('image3.jpg'), 'image3.jpg');

        $action = resolve(DeleteOldLocalImagesAction::class);
        $action->execute(1);

        $storage->assertExists([
            "$directory/image1.jpg",
            "$directory/image2.jpg",
            "$directory/image3.jpg",
        ]);

        $oldTimestamp = now()->subDays(3)->timestamp;

        touch($storage->path("$directory/image4.jpg"), $oldTimestamp);
        touch($storage->path("$directory/image5.jpg"), $oldTimestamp);
        touch($storage->path("$directory/image6.jpg"), $oldTimestamp);

        // First, confirm that new images are being created
        $storage->assertExists([
            "$directory/image4.jpg",
            "$directory/image5.jpg",
            "$directory/image6.jpg",
        ]);

        $action->execute(1);

        // These images should still be in the storage
        $storage->assertExists([
            "$directory/image1.jpg",
            "$directory/image2.jpg",
            "$directory/image3.jpg",
        ]);

        // These images should be deleted from the storage because they're old
        $storage->assertMissing([
            "$directory/image4.jpg",
            "$directory/image5.jpg",
            "$directory/image6.jpg",
        ]);
    }
}
