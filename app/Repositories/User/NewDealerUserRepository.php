<?php

namespace App\Repositories\User;

use App\Models\User\NewDealerUser;
use App\Repositories\RepositoryAbstract;

/**
 * Class NewDealerUserRepository
 * @package App\Repositories\User
 */
class NewDealerUserRepository extends RepositoryAbstract implements NewDealerUserRepositoryInterface
{
    /**
     * @param $params
     * @return NewDealerUser
     */
    public function create($params): NewDealerUser
    {
        $newDealerUser = new NewDealerUser();
        $newDealerUser->fill($params)->save();

        return $newDealerUser;
    }
}
