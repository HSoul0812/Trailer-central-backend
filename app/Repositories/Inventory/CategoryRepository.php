<?php

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Category;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Traits\SortTrait;

/**
 * Class CategoryRepository
 * @package App\Repositories\Inventory
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    use SortTrait;

    private $sortOrders = [
        'label' => [
            'field' => 'label',
            'direction' => 'DESC'
        ],
        '-label' => [
            'field' => 'label',
            'direction' => 'ASC'
        ],
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
    public function getAll($params, bool $paginated = false)
    {
        $query = Category::select('*');

        if (isset($params['entity_type_id'])) {
            $query = $query->where('entity_type_id', $params['entity_type_id']);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['search_term'])) {
            $query = $query->where('label', 'LIKE', '%' . $params['search_term'] . '%');
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        if ($paginated) {
            return $query->paginate($params['per_page'])->appends($params);
        }

        return $query->get();
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
