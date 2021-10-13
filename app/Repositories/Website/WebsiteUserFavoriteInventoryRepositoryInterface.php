<?php
namespace App\Repositories\Website;

use App\Repositories\Repository;

interface WebsiteUserFavoriteInventoryRepositoryInterface extends Repository {
    public function deleteBulk($params);
}
