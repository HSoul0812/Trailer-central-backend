<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;

abstract class MediaFileTransformer extends TransformerAbstract
{
    protected function getBaseUrl(): string
    {
        $urlMetadata = parse_url(env('AWS_URL'));

        return $urlMetadata['scheme'] . '://' . $urlMetadata['host'] . '/'.env('AWS_BUCKET');
    }
}
