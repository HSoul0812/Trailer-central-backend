<?php

namespace App\Repositories\Marketing;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\VirtualCard;
use App\Repositories\Traits\SortTrait;
use App\Traits\Repository\Pagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class VirtualCardRepository
 * 
 * @package App\Repositories\Marketing
 */
class VirtualCardRepository implements VirtualCardRepositoryInterface 
{
    use SortTrait, Pagination;

    private $sortOrders = [
        'expires_at' => [
            'field' => 'expires_at',
            'direction' => 'DESC'
        ],
        '-expires_at' => [
            'field' => 'expires_at',
            'direction' => 'ASC'
        ],
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
        $query = VirtualCard::where('id', '>', 0);

        if (!isset($params['sort'])) {
            $params['sort'] = '-expires_at';
        }

        if (isset($params['type'])) {
            $query = $query->where('type', $params['type']);
        }

        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
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