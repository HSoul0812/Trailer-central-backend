<?php

namespace App\Services\Integrations\TrailerCentral\Api\Image;

interface ImageServiceInterface
{
    public function uploadImage(int $dealerId, string $imagePath);
}
