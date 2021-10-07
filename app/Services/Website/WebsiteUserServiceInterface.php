<?php
namespace App\Services\Website;

interface WebsiteUserServiceInterface{
    /**
     * @param array $userInfo
     * @return mixed
     */
    public function createUser(array $userInfo);
}
