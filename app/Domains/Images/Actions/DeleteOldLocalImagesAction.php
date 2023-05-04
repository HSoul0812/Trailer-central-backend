<?php

namespace App\Domains\Images\Actions;

use App\Services\Integrations\TrailerCentral\Api\Image\ImageService;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Storage;

class DeleteOldLocalImagesAction
{
    private FilesystemAdapter $storage;

    public function __construct()
    {
        $this->storage = Storage::disk(ImageService::LOCAL_IMAGES_DISK);
    }

    public function execute(int $olderThanDays): void
    {
        $images = $this->storage->listContents(ImageService::LOCAL_IMAGES_DIRECTORY);

        foreach ($images as $image) {
            if ($image['type'] !== 'file') {
                continue;
            }

            $shouldDelete = $this->isImageOldEnoughToDelete(
                timestamp: $image['timestamp'],
                olderThanDays: $olderThanDays,
            );

            if (!$shouldDelete) {
                continue;
            }

            $this->storage->delete($image['path']);
        }
    }

    public function isImageOldEnoughToDelete(int $timestamp, int $olderThanDays): bool
    {
        return Carbon::createFromTimestamp($timestamp)->diffInDays() >= $olderThanDays;
    }
}
