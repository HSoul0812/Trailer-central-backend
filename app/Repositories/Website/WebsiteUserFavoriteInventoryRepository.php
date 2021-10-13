<?php
namespace App\Repositories\Website;

use App\Exceptions\NotImplementedException;
use App\Models\Website\User\WebsiteUserFavoriteInventory;
use App\Utilities\JsonApi\WithRequestQueryable;

class WebsiteUserFavoriteInventoryRepository implements WebsiteUserFavoriteInventoryRepositoryInterface {
    use WithRequestQueryable;

    private $websiteUserFavoriteInventory;

    public function __construct(WebsiteUserFavoriteInventory $websiteUserFavoriteInventory) {
        $this->websiteUserFavoriteInventory = $websiteUserFavoriteInventory;
    }

    public function create($params)
    {
        return $this->websiteUserFavoriteInventory->firstOrCreate($params);
    }

    public function update($params)
    {
        throw new NotImplementedException();
    }

    public function get($params)
    {
        throw new NotImplementedException();
    }

    public function delete($params)
    {
        throw new NotImplementedException();
    }

    public function deleteBulk($params) {
        $this->websiteUserFavoriteInventory
            ->where('website_user_id', $params['website_user_id'])
            ->whereIn('inventory_id', $params['inventory_ids'])
            ->delete();
    }

    public function getAll($params)
    {
        return $this->websiteUserFavoriteInventory
            ->where('website_user_id', $params['website_user_id'])
            ->get();
    }
}
