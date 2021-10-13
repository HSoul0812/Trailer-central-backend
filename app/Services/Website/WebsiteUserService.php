<?php
namespace App\Services\Website;

use App\Models\Website\User\WebsiteUser;
use App\Models\Website\User\WebsiteUserFavoriteInventory;
use App\Repositories\Website\WebsiteUserFavoriteInventoryRepository;
use App\Repositories\Website\WebsiteUserFavoriteInventoryRepositoryInterface;
use App\Repositories\Website\WebsiteUserRepository;
use App\Repositories\Website\WebsiteUserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\UnauthorizedException;


class WebsiteUserService implements WebsiteUserServiceInterface {
    /**
     * @var WebsiteUserRepository
     */
    private $websiteUserRepository;

    /**
     * @var WebsiteUserFavoriteInventoryRepository
     */
    private $websiteUserFavoriteInventoryRepository;

    /**
     * WebsiteUserService constructor.
     * @param WebsiteUserRepositoryInterface $websiteUserRepository
     * @param WebsiteUserFavoriteInventoryRepositoryInterface $websiteUserFavoriteInventoryRepository
     */
    public function __construct(
        WebsiteUserRepositoryInterface $websiteUserRepository,
        WebsiteUserFavoriteInventoryRepositoryInterface $websiteUserFavoriteInventoryRepository
    ) {
        $this->websiteUserRepository = $websiteUserRepository;
        $this->websiteUserFavoriteInventoryRepository = $websiteUserFavoriteInventoryRepository;
    }

    /**
     * @param array $userInfo
     * @return WebsiteUser
     */
    public function createUser(array $userInfo): WebsiteUser {
        $userInfo = array_replace([], $userInfo, ['token' => $this->generateUserToken()]);
        return $this->websiteUserRepository->create($userInfo);
    }

    /**
     * @param array $userInfo
     * @return WebsiteUser
     * @throws UnauthorizedException
     */
    public function loginUser(array $userInfo): WebsiteUser {
        $user = $this->websiteUserRepository->get($userInfo);
        if($user && $user->checkPassword($userInfo['password'])) {
            return $user;
        } else {
            abort(401, 'Failed to authenticate the user');
        }
    }

    public function addUserInventories($websiteUserId, array $inventoryIds) {
        $results = [];
        foreach($inventoryIds as $inventoryId) {
            $results[] = $this->websiteUserFavoriteInventoryRepository->create([
                'website_user_id' => $websiteUserId,
                'inventory_id' => $inventoryId
            ]);
        }
        return $results;
    }

    public function removeUserInventories($websiteUserId, array $inventories) {
        $this->websiteUserFavoriteInventoryRepository->deleteBulk([
            'website_user_id' => $websiteUserId,
            'inventory_ids' => $inventories
        ]);
    }

    public function getUserInventories($websiteUserId) {
        return $this->websiteUserFavoriteInventoryRepository->getAll(['website_user_id' => $websiteUserId]);
    }

    /**
     * @return string
     */
    private function generateUserToken() {
        $token = \Str::random(60);
        return hash('sha256', $token);
    }
}
