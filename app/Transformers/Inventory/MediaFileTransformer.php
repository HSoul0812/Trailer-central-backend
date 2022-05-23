<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;

abstract class MediaFileTransformer extends TransformerAbstract
{
    protected function getBaseUrl(): string
    {
        $urlMetadata = parse_url(config('services.aws.url'));

        return $urlMetadata['scheme'] . '://' . $urlMetadata['host'] . '/'. config('services.aws.bucket');
    }
}
