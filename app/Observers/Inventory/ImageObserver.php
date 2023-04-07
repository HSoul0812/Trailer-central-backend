<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\Image;

/**
 * @todo please ensure to remove the trigger `inventory_image_before_insert` when Legacy API is not used any more
 */
class ImageObserver
{
    /**
     * @return void
     */
    public function creating(Image $image)
    {
        $image->filename_without_overlay = $image->filename;
    }
}
