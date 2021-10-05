<?php
namespace App\Services\Website;

use App\Models\Website\DealerWebsiteUser;

interface WebsiteUserServiceInterface{
    /**
     * @param int $websiteId
     * @param array $userInfo
     * @return mixed
     */
    public function createUser(int $websiteId, array $userInfo);
}
