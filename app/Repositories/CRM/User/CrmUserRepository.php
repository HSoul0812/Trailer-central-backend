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
    public function create($params)
    {
        $crmUser = new CrmUser();

        $crmUser->fill($params)->save();

        return $crmUser;
    }

    public function update($params)
    {
        $crmUser = CrmUser::where(['user_id' => $params['user_id']])->first();

        $crmUser->fill($params)->save();

        return $crmUser;
    }

    public function get($params)
    {
        throw new NotImplementedException;
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
