<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\Account;
use App\Repositories\Traits\SortTrait;
use App\Traits\Repository\Pagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AccountRepository
 * 
 * @package App\Repositories\Marketing\Craigslist
 */
class AccountRepository implements VirtualCardRepositoryInterface 
{
    use SortTrait, Pagination;

    private $sortOrders = [
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        'updated_at' => [
            'field' => 'updated_at',
            'direction' => 'DESC'
        ],
        '-updated_at' => [
            'field' => 'updated_at',
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
        /** @var Builder $query */
        $query = Account::where('id', '>', 0);

        if (!isset($params['sort'])) {
            $params['sort'] = '-created_at';
        }

        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }

        if (isset($params['profile_id'])) {
            $query = $query->where('profile_id', $params['profile_id']);
        }

        if (isset($params['virtual_card_id'])) {
            $query = $query->where('virtual_card_id', $params['virtual_card_id']);
        }

        if (isset($params['per_page'])) {
            $query = $query->limit($params['per_page']);
        }

        // Sort Query Always Required
        return $this->addSortQuery($query, $params['sort'])->get();
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}