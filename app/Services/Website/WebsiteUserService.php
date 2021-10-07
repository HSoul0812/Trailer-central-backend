<?php
namespace App\Services\Website;

use App\Models\Website\DealerWebsiteUser;
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
     * WebsiteUserService constructor.
     * @param WebsiteUserRepositoryInterface $websiteUserRepository
     */
    public function __construct(
        WebsiteUserRepositoryInterface $websiteUserRepository
    ) {
        $this->websiteUserRepository = $websiteUserRepository;
    }

    /**
     * @param array $userInfo
     * @return DealerWebsiteUser
     */
    public function createUser(array $userInfo): DealerWebsiteUser {
        $userInfo = array_replace([], $userInfo, ['token' => $this->generateUserToken()]);
        return $this->websiteUserRepository->create($userInfo);
    }

    /**
     * @param array $userInfo
     * @return DealerWebsiteUser
     * @throws AuthenticationException
     */
    public function loginUser(array $userInfo): DealerWebsiteUser {
        $user = $this->websiteUserRepository->get($userInfo);
        if($user->checkPassword($userInfo['password'])) {
            return $user;
        } else {
            throw new AuthenticationException();
        }
    }

    /**
     * @return string
     */
    private function generateUserToken() {
        $token = \Str::random(60);
        return hash('sha256', $token);
    }
}
