<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\Session;
use App\Models\Marketing\Craigslist\Category;
use App\Repositories\Traits\SortTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Marketing\Craigslist\Profile;
use App\Traits\Repository\Pagination;

/**
 * Class ProfileInventory
 * @package App\Repositories\Inventory
 */
class ProfileRepository implements ProfileRepositoryInterface 
{
    use SortTrait, Pagination;

    private $sortOrders = [
        'profile' => [
            'field' => 'profile',
            'direction' => 'DESC'
        ],
        '-profile' => [
            'field' => 'profile',
            'direction' => 'ASC'
        ],
        'username' => [
            'field' => 'username',
            'direction' => 'DESC'
        ],
        '-username' => [
            'field' => 'username',
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
        $query = Profile::select(Profile::getTableName().'.id', 'profile', 'username', 'postCategory as category')
                        ->where('deleted', 0);

        if (!isset($params['type'])) {
            $params['type'] = 'inventory';
        }
        $query = $query->where('profile_type', $params['type']);

        if (!isset($params['sort'])) {
            $params['sort'] = '-profile';
        }

        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }

        // Include Slot ID?
        if (isset($params['slot_id']) && (int) $params['slot_id'] === Session::SLOT_SCHEDULER) {
            $query = $query->leftJoin(Category::getTableName(), Category::getTableName().'.category',
                                        '=', Profile::getTableName().'.postCategory')
                           ->where(Category::getTableName().'.grouping', Category::GROUP_BY_DEALER);
        }

        // Sort Query Always Required
        return $this->addSortQuery($query, $params['sort'])->get();
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}