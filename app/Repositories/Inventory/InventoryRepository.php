<?php

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Inventory;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;

/**
 * Class InventoryRepository
 * @package App\Repositories\Inventory
 */
class InventoryRepository implements InventoryRepositoryInterface
{
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
        'manufacturer' => [
            'field' => 'manufacturer',
            'direction' => 'DESC'
        ],
        '-manufacturer' => [
            'field' => 'manufacturer',
            'direction' => 'ASC'
        ],
        'vin' => [
            'field' => 'vin',
            'direction' => 'DESC'
        ],
        '-vin' => [
            'field' => 'vin',
            'direction' => 'ASC'
        ],
        'true_cost' => [
            'field' => 'true_cost',
            'direction' => 'DESC'
        ],
        '-true_cost' => [
            'field' => 'true_cost',
            'direction' => 'ASC'
        ],
        'fp_balance' => [
            'field' => 'fp_balance',
            'direction' => 'DESC'
        ],
        '-fp_balance' => [
            'field' => 'fp_balance',
            'direction' => 'ASC'
        ],
        'fp_interest_paid' => [
            'field' => 'fp_interest_paid',
            'direction' => 'DESC'
        ],
        '-fp_interest_paid' => [
            'field' => 'fp_interest_paid',
            'direction' => 'ASC'
        ],
        'fp_committed' => [
            'field' => 'fp_committed',
            'direction' => 'DESC'
        ],
        '-fp_committed' => [
            'field' => 'fp_committed',
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
     * @return Inventory
     */
    public function update($params)
    {
        $item = Inventory::findOrFail($params['inventory_id']);

        $item->fill($params)->save();

        return $item;
    }

    /**
     * @param $params
     * @return Inventory
     */
    public function get($params)
    {
        return Inventory::findOrFail($params['id']);
    }

    /**
     * @param $params
     * @return boolean
     */
    public function delete($params)
    {
        /** @var Inventory $item */
        $item = Inventory::findOrFail($params['id']);

        DB::transaction(function() use (&$item, $params) {
            $item->attributeValues()->delete();
            $item->features()->delete();
            $item->clapps()->delete();
            $item->lotVantageInventory()->delete();

            $item->delete();
        });

        return true;
    }

    /**
     * @param $params
     * @param bool $withDefault
     * @param bool $paginated
     * @return Collection
     */
    public function getAll($params, bool $withDefault = true, bool $paginated = false)
    {
        $query = Inventory::select('*');

        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if ($withDefault) {
            $query = $query->where(self::DEFAULT_GET_PARAMS[self::CONDITION_AND_WHERE]);
        }

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query = $query->where($params[self::CONDITION_AND_WHERE]);
        }

        if (isset($params['only_floorplanned']) && !empty($params['only_floorplanned'])) {
            /**
             * Filter only floored inventories to pay
             * https://crm.trailercentral.com/accounting/floorplan-payment
             */
            $query = $query->whereNotNull('bill_id')
                ->where([
                    ['is_floorplan_bill', '=', 1],
                    ['fp_vendor', '>', 0],
                    ['true_cost', '>', 0],
                    ['fp_balance', '>', 0]
                ]);
        }

        if (isset($params['search_term'])) {
            $query = $query->where(function($q) use ($params) {
                $q->where('stock', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('title', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('description', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('vin', 'LIKE', '%' . $params['search_term'] . '%');
            });
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
