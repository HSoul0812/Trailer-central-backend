<?php

namespace App\Services\CRM\Email;

interface MosaicoServiceInterface {

    const IMAGE_METHOD_PLACEHOLDER = 'placeholder';
    const IMAGE_METHOD_RESIZE = 'resize';
    const IMAGE_METHOD_COVER = 'cover';

    const THUMBNAIL_WIDTH = 90;
    const THUMBNAIL_HEIGHT = 90;

    const IMAGE_GALLERY_FOLDER = 'mosaico/user-gallery/{dealerId}';
    const IMAGE_THUMBNAIL_FOLDER = 'mosaico/user-thumbnail/{dealerId}';
    const IMAGE_STATIC_FOLDER = 'mosaico/user-static/{dealerId}';
}