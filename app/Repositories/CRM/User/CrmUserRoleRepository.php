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
    /**
     * @param array $params
     * @return UserRole
     */
    public function create($params): UserRole
    {
        $crmUser = new UserRole();

        $crmUser->fill($params)->save();

        return $crmUser;
    }

    /**
     * @throws NotImplementedException
     */
    public function update($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @return UserRole|null
     */
    public function get($params): ?UserRole
    {
        return UserRole::where(['user_id' => $params['user_id']])->first();
    }

    /**
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @throws NotImplementedException
     */
    public function getAll($params)
    {
        throw new NotImplementedException;
    }
}
