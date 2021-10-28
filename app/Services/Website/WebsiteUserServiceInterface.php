<?php
namespace App\Services\Website;

use App\Models\Website\User\WebsiteUser;
use Illuminate\Database\Eloquent\Collection;

interface WebsiteUserServiceInterface{
    /**
     * @param array $userInfo
     * @return mixed
     */
    public function createUser(array $userInfo): WebsiteUser;

    public function loginUser(array $userInfo): WebsiteUser;

    public function addUserInventories(int $websiteUserId, array $inventoryIds): array;

    public function removeUserInventories(int $websiteUserId, array $inventories): void;

    public function getUserInventories(int $websiteUserId): Collection;

}
