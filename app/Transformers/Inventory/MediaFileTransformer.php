<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;

abstract class MediaFileTransformer extends TransformerAbstract
{
    protected function getBaseUrl(): string
    {
        return env('AWS_URL').env('AWS_BUCKET');
    }
}
