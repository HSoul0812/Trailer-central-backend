<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\Session;
use App\Models\User\User;
use App\Models\User\DealerClapp;
use App\Repositories\Traits\SortTrait;
use App\Traits\Repository\Pagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class DealerRepository
 * 
 * @package App\Repositories\Marketing
 */
class DealerRepository implements DealerRepositoryInterface 
{
    use SortTrait, Pagination;

    private $sortOrders = [
        'date_scheduled' => [
            'field' => 'clapp_session.session_scheduled',
            'direction' => 'DESC'
        ],
        '-date_scheduled' => [
            'field' => 'clapp_session.session_scheduled',
            'direction' => 'ASC'
        ],
        'date_started' => [
            'field' => 'clapp_session.session_started',
            'direction' => 'DESC'
        ],
        '-date_started' => [
            'field' => 'clapp_session.session_started',
            'direction' => 'ASC'
        ]
    ];

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function update($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function get($params)
    {
        return DealerClapp::findOrFail($params['dealer_id']);
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @return Collection
     */
    public function getAll($params)
    {
        /** @var  Builder $query */
        $query = DealerClapp::leftJoin(User::GetTableName(),
                                DealerClapp::getTableName() . '.dealer_id', '=',
                                User::getTableName() . '.dealer_id')
                    ->whereNotNull(User::getTableName() . '.stripe_id')
                    ->where(User::getTableName() . '.state', User::STATUS_ACTIVE)
                    ->leftJoin(Session::GetTableName(),
                                DealerClapp::getTableName() . '.dealer_id', '=',
                                Session::getTableName() . '.session_dealer_id')
                    ->groupBy(DealerClapp::getTableName() . '.dealer_id');

        if (!isset($params['type'])) {
            $params['type'] = 'now';
        }

        if (isset($params['has_balance'])) {
            $query = $query->leftJoin(Balance::GetTableName(),
                                DealerClapp::getTableName() . '.dealer_id', '=',
                                Balance::getTableName() . '.dealer_id')
                           ->whereNotNull('balance')->where('balance', '>', 0);
        }

        if($params['type'] === 'now') {
            $query->where(Session::getTableName() . '.session_scheduled', '<=', DB::raw('NOW()'));
        } elseif($params['type'] === 'posted') {
            $query->where(Session::getTableName() . '.status', '=', 'done')
                  ->where(Session::getTableName() . '.state', '=', 'done');
        }

        if (!isset($params['sort'])) {
            $params['sort'] = '-date_scheduled';
        }

        // Sort Query Always Required
        return $this->addSortQuery($query, $params['sort'])->get();
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}