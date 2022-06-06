<?php
namespace App\Services\Website;

use App\Models\Website\User\WebsiteUser;
use App\Models\Website\User\WebsiteUserSearchResult;
use App\Repositories\Website\WebsiteUserFavoriteInventoryRepository;
use App\Repositories\Website\WebsiteUserRepository;
use App\Repositories\Website\WebsiteUserSearchResultRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\UnauthorizedException;


class WebsiteUserService implements WebsiteUserServiceInterface {
    /**
     * @var WebsiteUserRepository
     */
    private $websiteUserRepository;

    /** @var WebsiteUserSearchResultRepository */
    private $websiteUserSearchResultRepository;

    /**
     * @var WebsiteUserFavoriteInventoryRepository
     */
    private $websiteUserFavoriteInventoryRepository;

    /**
     * @param WebsiteUserRepository $websiteUserRepository
     * @param WebsiteUserSearchResultRepository $websiteUserSearchResultRepository
     * @param WebsiteUserFavoriteInventoryRepository $websiteUserFavoriteInventoryRepository
     */
    public function __construct(WebsiteUserRepository $websiteUserRepository, WebsiteUserSearchResultRepository $websiteUserSearchResultRepository, WebsiteUserFavoriteInventoryRepository $websiteUserFavoriteInventoryRepository)
    {
        $this->websiteUserRepository = $websiteUserRepository;
        $this->websiteUserSearchResultRepository = $websiteUserSearchResultRepository;
        $this->websiteUserFavoriteInventoryRepository = $websiteUserFavoriteInventoryRepository;
    }

    /**
     * @param array $userInfo
     * @return WebsiteUser
     */
    public function createUser(array $userInfo): WebsiteUser {
        $userInfo = array_replace([], $userInfo, ['token' => $this->generateUserToken(),'last_login' => now()]);
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
            $user->update(['last_login' => now()]);
            return $user;
        } else {
            abort(401, 'Failed to authenticate the user');
        }
    }

    public function addUserInventories(int $websiteUserId, array $inventoryIds): array {
        $results = [];
        foreach($inventoryIds as $inventoryId) {
            $results[] = $this->websiteUserFavoriteInventoryRepository->create([
                'website_user_id' => $websiteUserId,
                'inventory_id' => $inventoryId
            ]);
        }
        return $results;
    }

    public function removeUserInventories(int $websiteUserId, array $inventories): void {
        $this->websiteUserFavoriteInventoryRepository->deleteBulk([
            'website_user_id' => $websiteUserId,
            'inventory_ids' => $inventories
        ]);
    }

    public function getUserInventories(int $websiteUserId): Collection {
        return $this->websiteUserFavoriteInventoryRepository->getAll(['website_user_id' => $websiteUserId]);
    }

    /**
     * @param string $search_url
     * @param int $websiteUserId
     * @return WebsiteUserSearchResult
     * @throws \LogicException When a search result already saved.
     */
    public function addSearchUrlToUser(array $params): ?WebsiteUserSearchResult {

        $searchResult = $this->websiteUserSearchResultRepository->get([
            'website_user_id' => $params['website_user_id'],
            'search_url' => $params['search_url']
        ]);

        if (!empty($searchResult)) {
           throw new \LogicException("Search result already saved before.");
        }

        return $this->websiteUserSearchResultRepository->create([
            'website_user_id' => $params['website_user_id'],
            'search_url' => $params['search_url'],
            'summary' => $params['summary'],
        ]);
    }

    /**
     * @param array $params
     * @return void
     */
    public function removeSearchResult(array $params): void
    {
        if (empty($params['search_id'])) {
            throw new \LogicException("Missing saved search ID.");
        }

        $this->websiteUserSearchResultRepository->delete($params);
    }

    /**
     * @param array $params
     * @return object
     */
    public function getUserSearchResults(array $params): object
    {
        return $this->websiteUserSearchResultRepository->getAll($params);
    }


    /**
     * @return string
     */
    private function generateUserToken(): string {
        $token = \Str::random(60);
        return hash('sha256', $token);
    }
}
