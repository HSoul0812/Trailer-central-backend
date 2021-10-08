<?php
namespace App\Services\Website;

use App\Models\Website\User\WebsiteUser;
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

    /**
     * @return string
     */
    private function generateUserToken() {
        $token = \Str::random(60);
        return hash('sha256', $token);
    }
}
