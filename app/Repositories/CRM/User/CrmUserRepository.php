<?php

namespace App\Repositories\CRM\User;

use App\Exceptions\NotImplementedException;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;

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
     * Get All CRM Users
     * 
     * array $params
     * return Collection<CrmUser>
     */
    public function getAll($params)
    {
        // Initialize Query
        $query = CrmUser::leftJoin(NewDealerUser::getTableName(),
                                   NewDealerUser::getTableName() . '.user_id',
                                   '=', CrmUser::getTableName() . '.user_id')
                        ->where(CrmUser::getTableName() . '.user_id', '>', 0);

        // Check Active By Default
        if (!isset($params['is_active'])) {
            $query = $query->where('active', 1);
        }
        // If Is Active Provided, Use Value
        elseif(isset($params['is_active'])) {
            $query = $query->where('active', $params['is_active']);
        }

        // Find By User ID?
        if (isset($params['user_id'])) {
            $query = $query->where(CrmUser::getTableName() . '.user_id', $params['user_id']);
        }

        // Find By Dealer ID?
        if (isset($params['dealer_id'])) {
            $query = $query->where(NewDealerUser::getTableName() . '.id', $params['dealer_id']);
        } else {
            // Find By Min Dealer ID?
            if(!empty($params['min_dealer_id'])) {
                $query = $query->where(NewDealerUser::getTableName() . '.id', '>=', $params['min_dealer_id']);
            }

            // Find Max Dealer ID
            if(!empty($params['max_dealer_id'])) {
                $query = $query->where(NewDealerUser::getTableName() . '.id', '<=', $params['max_dealer_id']);
            }
        }

        // Hot Potato Enabled?
        if(isset($params['enable_hot_potato'])) {
            $query = $query->where('enable_hot_potato', $params['enable_hot_potato']);
        }

        // Return Dealers Collection
        return $query->with('newDealerUser')->get();
    }
}
