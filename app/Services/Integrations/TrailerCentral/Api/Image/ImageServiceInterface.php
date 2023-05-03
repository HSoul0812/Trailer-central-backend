<?php

namespace App\Services\Integrations\TrailerCentral\Api\Image;

use Illuminate\Http\UploadedFile;

interface ImageServiceInterface
{
    public function uploadImage(int $dealerId, string $imagePath);

    /**
     * Return the URL of the uploaded image.
     */
    public function uploadLocalImage(UploadedFile $uploadedFile): string;

    /**
     * Delete the old images.
     */
    public function deleteOldLocalImages(int $olderThanDays): void;
}
