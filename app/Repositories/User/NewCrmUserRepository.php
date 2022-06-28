<?php

namespace App\Repositories\User;

use App\Models\User\CrmUser;
use App\Repositories\RepositoryAbstract;

/**
 * Class NewCrmUserRepository
 * @package App\Repositories\User
 */
class NewCrmUserRepository extends RepositoryAbstract implements NewCrmUserRepositoryInterface
{
    /**
     * @param $params
     * @return CrmUser
     */
    public function create($params): NewCrmUser
    {
        $crmUser = new CrmUser();
        $crmUser->fill($params)->save();

        return $crmUser;
    }
}
