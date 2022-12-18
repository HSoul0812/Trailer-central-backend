<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Image;

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
}