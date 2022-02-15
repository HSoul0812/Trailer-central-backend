<?php

namespace App\Repositories\CRM\User;

use App\Exceptions\NotImplementedException;
use App\Models\User\CrmUser;

/**
 * Class CrmUserRepository
 * @package App\Repositories\User
 */
class CrmUserRepository implements CrmUserRepositoryInterface
{
    /**
     * @param array $params
     * @return CrmUser
     */
    public function create($params): CrmUser
    {
        $crmUser = new CrmUser();

        $crmUser->fill($params)->save();

        return $crmUser;
    }

    /**
     * @param array $params
     * @return CrmUser
     */
    public function update($params): CrmUser
    {
        $crmUser = CrmUser::where(['user_id' => $params['user_id']])->first();

        $crmUser->fill($params)->save();

        return $crmUser;
    }

    /**
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
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
