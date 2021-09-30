<?php

namespace App\Transformers\Website;

use App\Models\Website\Website;
use League\Fractal\TransformerAbstract;

/**
 * Class WebsiteTransformer
 * @package App\Transformers\Website
 */
class WebsiteTransformer extends TransformerAbstract
{
    public function transform(Website $website): array
    {
        return [
            'id' => $website->id,
            'domain' => $website->domain,
            'type' => $website->type,
            'date_created' => $website->date_created,
            'is_active' => $website->is_active,
        ];
    }
}
