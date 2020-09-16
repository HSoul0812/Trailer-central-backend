<?php

namespace App\Transformers\Website;

use App\Models\Website\Website;
use League\Fractal\TransformerAbstract;


class WebsiteTransformer extends TransformerAbstract
{
    public function transform(Website $website)
    {
        return [
            'domain' => $website->domain,
        ];
    }
}
