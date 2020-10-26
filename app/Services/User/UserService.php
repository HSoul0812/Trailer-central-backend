<?php


namespace App\Services\User;


use App\Repositories\User\UserRepositoryInterface;

class UserService
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function setAdminPasswd($dealerId, $passwd)
    {
        return $this->userRepository->setAdminPasswd($dealerId, $passwd);
    }
}
