<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\NotImplementedException;
use App\Models\User\DealerClapp;
use App\Models\Marketing\Craigslist\Session;
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
            'field' => 'clapp_session.date_scheduled',
            'direction' => 'DESC'
        ],
        '-date_scheduled' => [
            'field' => 'clapp_session.date_scheduled',
            'direction' => 'ASC'
        ],
        'date_started' => [
            'field' => 'clapp_session.date_started',
            'direction' => 'DESC'
        ],
        '-date_started' => [
            'field' => 'clapp_session.date_started',
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
        throw new NotImplementedException;
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
        $query = DealerClapp::where('deleted', 0)->with('dealerActive')
                    ->leftJoin(Session::GetTableName(),
                                DealerClapp::getTableName() . '.dealer_id', '=',
                                Session::getTableName() . '.session_dealer_id')
                    ->groupBy(DealerClapp::getTableName() . '.dealer_id');

        if (!isset($params['type'])) {
            $params['type'] = 'now';
        }

        if($params['type'] === 'now') {
            $query->where(Session::getTableName() . '.clapp_session', '<=', DB::raw('NOW()'));
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