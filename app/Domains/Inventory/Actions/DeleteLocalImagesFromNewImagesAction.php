<?php

namespace App\Domains\Inventory\Actions;

use App\Services\Integrations\TrailerCentral\Api\Image\ImageService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Storage;
use Str;
use Throwable;

class DeleteLocalImagesFromNewImagesAction
{
    private FilesystemAdapter $storage;

    public function __construct()
    {
        $this->storage = Storage::disk('local_tmp');
    }

    public function execute(Collection $newImages): Collection
    {
        // No need to waste CPU time if the given collection is empty, just return empty collection back
        if ($newImages->isEmpty()) {
            return collect([]);
        }

        return $newImages
            // Only get the one that has URL
            ->filter(fn (array $image) => data_get($image, 'url') !== null)

            // Get only url string from each one
            ->map(fn (array $image) => data_get($image, 'url'))

            // Get only image path for each one
            ->map(fn (string $imageUrl) => $this->urlToLocalImagePath($imageUrl))

            // Remove the null one (if it's not local image, it will be null)
            ->filter()

            // Delete each of them
            ->map(fn (string $imagePath) => $this->deleteImageByPath($imagePath))

            // Remove null (just a precaution)
            ->filter();
    }

    /**
     * @param string $imageUrl Example: https://trailertrader.com/upload/tmp/images/CpKhEksurmAqQ92o3GpiLRHBBZYXei3Hf7KEUBVQ.jpg
     */
    public function urlToLocalImagePath(string $imageUrl): ?string
    {
        $imageUrlStringable = Str::of($imageUrl);

        // Do nothing if the image url is not from this domain
        if (!$imageUrlStringable->startsWith(config('app.url'))) {
            return null;
        }

        $imageUrlStringable = $imageUrlStringable->remove(config('app.url'))->ltrim('/');

        $directory = ImageService::LOCAL_IMAGES_DIRECTORY;

        $re = sprintf("/upload\/tmp\/%s\/(?<filepath>.*)/", $directory);

        $match = preg_match($re, (string) $imageUrlStringable, $matches, PREG_OFFSET_CAPTURE);

        if ($match !== 1) {
            return null;
        }

        $filename = data_get($matches, 'filepath.0');

        if ($filename === null) {
            return null;
        }

        return "$directory/$filename";
    }

    public function deleteImageByPath(string $imagePath): ?string
    {
        try {
            $imageUrl = $this->storage->url($imagePath);

            $this->storage->delete($imagePath);

            return $imageUrl;
        } catch (Throwable) {
            // Do nothing in case of deletion failure
        }

        return null;
    }
}
