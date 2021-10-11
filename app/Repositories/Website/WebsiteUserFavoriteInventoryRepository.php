<?php
namespace App\Repositories\Website;

use App\Exceptions\NotImplementedException;
use App\Models\Website\User\WebsiteUserFavoriteInventory;

class WebsiteUserFavoriteInventoryRepository implements WebsiteUserFavoriteInventoryRepositoryInterface {

    public function create($params)
    {

        WebsiteUserFavoriteInventory::create();

        throw new NotImplementedException();
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

    public function getAll($params)
    {
        throw new NotImplementedException();
    }
}
