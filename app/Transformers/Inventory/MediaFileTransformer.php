<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use App\Traits\S3\S3Helper;
use League\Fractal\TransformerAbstract;

/**
 * Class MediaFileTransformer
 * @package App\Transformers\Inventory
 */
abstract class MediaFileTransformer extends TransformerAbstract
{
    use S3Helper;

    /**
     * @return string
     */
    protected function getBaseUrl(): string
    {
        return $this->getS3BaseUrl();
    }
}
