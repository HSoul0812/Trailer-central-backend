<?php

namespace App\Transformers\User;

use League\Fractal\TransformerAbstract;
use App\Models\User\WebsiteImage;

class DealerImageTransformer extends TransformerAbstract
{
    public function transform(WebsiteImage $image): array
    {
        return [
            'id' => $image->identifier,
            'website_id' => $image->website_id,
            'title' => $image->title,
            'image' => $image->image,
            'description' => $image->description,
            'link' => $image->link,
            'sort_order' => $image->sort_order,
            'date_created' => $image->date_created,
            'is_active' => $image->is_active,
            'promo_id' => $image->promo_id,
            'expires_at' => $image->expires_at
        ];
    }
}
