<?php

namespace App\Transformers\Website;

use App\Models\Website\User\WebsiteUser;
use App\Models\Website\User\WebsiteUserFavoriteInventory;
use App\Models\Website\Website;
use League\Fractal\TransformerAbstract;

/**
 * Class WebsiteTransformer
 * @package App\Transformers\Website
 */
class WebsiteUserFavoriteInventoryTransformer extends TransformerAbstract
{
    public function transform(WebsiteUserFavoriteInventory $user): array
    {
        return [
            'website_user_id' => $user->website_user_id,
            'inventory_id' => $user->inventory_id
        ];
    }
}
