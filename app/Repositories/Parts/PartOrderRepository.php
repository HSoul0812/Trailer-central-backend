<?php

namespace App\Repositories\Parts;

use App\Models\Parts\PartOrder;
use Illuminate\Database\Query\Builder;
use App\Exceptions\NotImplementedException;
use App\Repositories\Traits\SortTrait;

/**
 * Part Order Repository
 *
 * @author David A Conway Jr.
 */
class PartOrderRepository implements PartOrderRepositoryInterface {

    use SortTrait;

    private $sortOrders = [
        'title' => [
            'field' => 'title',
            'direction' => 'DESC'
        ],
        '-title' => [
            'field' => 'title',
            'direction' => 'ASC'
        ],
        'price' => [
            'field' => 'price',
            'direction' => 'DESC'
        ],
        '-price' => [
            'field' => 'price',
            'direction' => 'ASC'
        ],
        'sku' => [
            'field' => 'sku',
            'direction' => 'DESC'
        ],
        '-sku' => [
            'field' => 'sku',
            'direction' => 'ASC'
        ],
        'dealer_cost' => [
            'field' => 'dealer_cost',
            'direction' => 'DESC'
        ],
        '-dealer_cost' => [
            'field' => 'dealer_cost',
            'direction' => 'ASC'
        ],
        'msrp' => [
            'field' => 'msrp',
            'direction' => 'DESC'
        ],
        '-msrp' => [
            'field' => 'msrp',
            'direction' => 'ASC'
        ],
        'subcategory' => [
            'field' => 'subcategory',
            'direction' => 'DESC'
        ],
        '-subcategory' => [
            'field' => 'subcategory',
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
        'stock' => [
            'field' => 'stock',
            'direction' => 'DESC'
        ],
        '-stock' => [
            'field' => 'stock',
            'direction' => 'ASC'
        ]
    ];

    public function create($params) {
        throw new NotImplementedException();
    }

    public function delete($params) {
        throw new NotImplementedException();
    }

    public function get($params) {
        return PartOrder::findOrFail($params['id'])->load('bins.bin');
    }

    public function getAll($params)
    {
        /** @var Builder $query */
        $query = PartOrder::where('id', '>', 0);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['status'])) {
            $query = $query->whereIn('status', $params['status']);
        }

        if (isset($params['dealer_id'])) {
            $query = $query->whereIn('dealer_id', $params['dealer_id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException();
    }
}
