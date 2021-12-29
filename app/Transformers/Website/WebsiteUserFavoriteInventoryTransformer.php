<?php

namespace App\Transformers\Website;

use App\Models\Website\User\WebsiteUser;
use App\Models\Website\User\WebsiteUserFavoriteInventory;
use App\Models\Website\Website;
use App\Transformers\Inventory\InventoryTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class WebsiteTransformer
 * @package App\Transformers\Website
 */
class WebsiteUserFavoriteInventoryTransformer extends TransformerAbstract
{
    private $inventoryTransformer;

    public function __construct() {
        $this->inventoryTransformer = new InventoryTransformer();
    }

    public function transform(WebsiteUserFavoriteInventory $inventory): array
    {
        return [
            'website_user_id' => $inventory->website_user_id,
            'inventory_id' => $inventory->inventory_id,
            'inventory' => $this->inventoryTransformer->transform($inventory->inventory)
         ];
    }
}
