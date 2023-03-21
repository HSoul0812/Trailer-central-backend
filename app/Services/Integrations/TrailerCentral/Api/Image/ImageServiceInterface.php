<?php

namespace App\Services\Integrations\TrailerCentral\Api\Image;

use App\Models\WebsiteUser\WebsiteUser;
use Illuminate\Http\UploadedFile;

interface ImageServiceInterface
{
    public function uploadImage(int $dealerId, string $imagePath);

    /**
     * Return the URL of the uploaded image
     *
     * @param UploadedFile $uploadedFile
     * @return string
     */
    public function uploadLocalImage(UploadedFile $uploadedFile): string;

    /**
     * Delete the old images
     *
     * @param int $olderThanDays
     * @return void
     */
    public function deleteOldLocalImages(int $olderThanDays): void;
}
