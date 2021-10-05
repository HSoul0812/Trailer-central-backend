<?php
namespace App\Services\Website;

use App\Repositories\Website\WebsiteUserRepository;
use App\Repositories\Website\WebsiteUserRepositoryInterface;


class WebsiteUserService implements WebsiteUserServiceInterface {
    /**
     * @var WebsiteUserRepository
     */
    private $websiteUserRepository;

    /**
     * WebsiteUserService constructor.
     * @param WebsiteUserRepositoryInterface $websiteUserRepository
     */
    public function __construct(
        WebsiteUserRepositoryInterface $websiteUserRepository
    ) {
        $this->websiteUserRepository = $websiteUserRepository;
    }

    public function createUser(int $websiteId, array $userInfo) {
        $userInfo = array_replace([], $userInfo, ['token' => $this->generateUserToken()]);
        return $this->websiteUserRepository->create($userInfo);
    }

    private function generateUserToken() {
        $token = \Str::random(60);
        return hash('sha256', $token);
    }
}
