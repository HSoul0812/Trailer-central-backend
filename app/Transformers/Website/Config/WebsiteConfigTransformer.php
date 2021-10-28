<?php

namespace App\Transformers\Website\Config;

use App\Models\Website\Config\WebsiteConfig;
use League\Fractal\TransformerAbstract;

class WebsiteConfigTransformer extends TransformerAbstract
{

    public function transform(WebsiteConfig $WebsiteConfig)
    {
        return [
            'id' => (int)$WebsiteConfig->id,
            'website_id' => (int)$WebsiteConfig->website_id,
            'key' => (string)$WebsiteConfig->key,
            'value' => (string)$WebsiteConfig->value
        ];
    }
}