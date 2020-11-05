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
        'status' => [
            'field' => 'status',
            'direction' => 'DESC'
        ],
        '-status' => [
            'field' => 'status',
            'direction' => 'ASC'
        ],
        'fulfillment' => [
            'field' => 'fulfillment_type',
            'direction' => 'DESC'
        ],
        '-fulfillment' => [
            'field' => 'fulfillment_type',
            'direction' => 'ASC'
        ],
        'email' => [
            'field' => 'email_address',
            'direction' => 'DESC'
        ],
        '-email' => [
            'field' => 'email_address',
            'direction' => 'ASC'
        ],
        'phone' => [
            'field' => 'phone_number',
            'direction' => 'DESC'
        ],
        '-phone' => [
            'field' => 'phone_number',
            'direction' => 'ASC'
        ],
        'shipto' => [
            'field' => 'shipto_name',
            'direction' => 'DESC'
        ],
        '-shipto' => [
            'field' => 'shipto_name',
            'direction' => 'ASC'
        ],
        'billto' => [
            'field' => 'billto_name',
            'direction' => 'DESC'
        ],
        '-billto' => [
            'field' => 'billto_name',
            'direction' => 'ASC'
        ],
        'subtotal' => [
            'field' => 'subtotal',
            'direction' => 'DESC'
        ],
        '-subtotal' => [
            'field' => 'subtotal',
            'direction' => 'ASC'
        ],
        'tax' => [
            'field' => 'tax',
            'direction' => 'DESC'
        ],
        '-tax' => [
            'field' => 'tax',
            'direction' => 'ASC'
        ],
        'shipping' => [
            'field' => 'shipping',
            'direction' => 'DESC'
        ],
        '-shipping' => [
            'field' => 'shipping',
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
        ],
    ];

    public function create($params) {
        throw new NotImplementedException();
    }

    public function delete($params) {
        throw new NotImplementedException();
    }

    public function get($params) {
        return PartOrder::findOrFail($params['id']);
    }

    public function getAll($params)
    {
        /** @var Builder $query */
        $query = PartOrder::where('dealer_id', $params['dealer_id']);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['website_id'])) {
            $query = $query->whereIn('website_id', $params['website_id']);
        }

        if (isset($params['status'])) {
            $query = $query->whereIn('status', $params['status']);
        }

        if (isset($params['fulfillment'])) {
            $query = $query->whereIn('fulfillment_type', $params['fulfillment']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException();
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
