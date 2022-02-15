<?php

namespace App\Repositories\User;

use App\Models\User\NewUser;
use App\Repositories\RepositoryAbstract;

/**
 * Class NewUserRepository
 * @package App\Repositories\User
 */
class NewUserRepository extends RepositoryAbstract implements NewUserRepositoryInterface
{
    /**
     * @param $params
     * @return NewUser
     */
    public function create($params): NewUser
    {
        $newUser = new NewUser();
        $newUser->fill($params)->save();

        return $newUser;
    }
}
