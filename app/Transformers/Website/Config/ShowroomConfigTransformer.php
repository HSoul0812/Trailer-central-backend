<?php

namespace App\Transformers\Website\Config;

use App\Models\Website\Config\WebsiteConfig;
use League\Fractal\TransformerAbstract;

class ShowroomConfigTransformer extends TransformerAbstract
{

    public function transform(WebsiteConfig $WebsiteConfig): array
    {
        return [
            'id' => $WebsiteConfig->id,
            'website_id' => $WebsiteConfig->website_id,
            'key' => $WebsiteConfig->key,
            'value' => $WebsiteConfig->value
        ];
    }
}