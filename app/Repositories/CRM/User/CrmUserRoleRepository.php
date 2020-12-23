<?php

namespace App\Repositories\CRM\User;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\User\UserRole;

/**
 * Class CrmUserRoleRepository
 * @package App\Repositories\CRM\User
 */
class CrmUserRoleRepository implements CrmUserRoleRepositoryInterface
{
    public function create($params)
    {
        $crmUser = new UserRole();

        $crmUser->fill($params)->save();

        return $crmUser;
    }

    public function update($params)
    {
        throw new NotImplementedException;
    }

    public function get($params)
    {
        return UserRole::where(['user_id' => $params['user_id']])->first();
    }

    public function delete($params)
    {
        throw new NotImplementedException;
    }

    public function getAll($params)
    {
        throw new NotImplementedException;
    }
}
