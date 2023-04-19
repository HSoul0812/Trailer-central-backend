<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Image;
use App\Models\User\User;

interface ImageServiceInterface
{
    public function saveOverlay(Image $image, string $filename): void;

    public function tryToRestoreOriginalImage(Image $image): void;

    public function tryToRestoreImageOverlay(Image $image): void;

    /**
     * @param string $filename
     * @return string a hash based on filename
     */
    public function getFileHash(string $filename): string;

    /**
     * @param  array{
     *     dealer_id:int,
     *     inventory_id: int,
     *     overlay_logo: string,
     *     overlay_logo_position: string,
     *     overlay_logo_width: int,
     *     overlay_upper: string,
     *     overlay_upper_bg: string,
     *     overlay_upper_alpha: string,
     *     overlay_upper_text: string,
     *     overlay_upper_size: int,
     *     overlay_upper_margin: string,
     *     overlay_lower: string,
     *     overlay_lower_bg: string,
     *     overlay_lower_alpha: string,
     *     overlay_lower_text: string,
     *     overlay_lower_size: int,
     *     overlay_lower_margin: string,
     *     overlay_default: int,
     *     overlay_enabled: int,
     *     dealer_overlay_enabled: int,
     *     overlay_text_dealer: string,
     *     overlay_text_phone: string,
     *     country: string,
     *     overlay_text_location: string,
     *     overlay_updated_at: string
     *     }  $params
     * @return User
     */
    public function updateOverlaySettings(array $params): User;
}
