<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Image;
use App\Models\User\User;

/**
 * Interface ImageServiceInterface
 *
 * @package App\Services\Inventory
 */
interface ImageServiceInterface 
{
    /**
     * @param Image $image
     * @param string $filename
     * @return void
     */
    public function saveOverlay(Image $image, string $filename): void;

    /**
     * @param Image $image
     * @return void
     */
    public function resetOverlay(Image $image): void;

    /**
     * @param string $filename
     * @return string
     */
    public function getFileHash(string $filename): string;

    /**
     * @param array $params
     * @return User
     */
    public function updateOverlaySettings(array $params): User; 
}