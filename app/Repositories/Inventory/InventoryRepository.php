<?php

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Inventory;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Traits\SortTrait;

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
        ],
        'fp_vendor' => [
            'field' => 'fp_vendor',
            'direction' => 'DESC'
        ],
        '-fp_vendor' => [
            'field' => 'fp_vendor',
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
     * @throws NotImplementedException
     */
    public function get($params)
    {
        return Inventory::findOrFail($params['id']);
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
     * @param bool $withDefault
     * @return Collection
     */
    public function getAll($params, bool $withDefault = true, bool $paginated = false)
    {
        $query = Inventory::select('*');
        
        $query->where('status', '<>', Inventory::STATUS_QUOTE);
        
        if (isset($params['dealer_id'])) {
            $query = $query->where('inventory.dealer_id', $params['dealer_id']);
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
        
        if (isset($params['floorplan_vendor'])) {
            $query = $query->where('fp_vendor', $params['floorplan_vendor']);
        }
     
        if (isset($params['search_term'])) {
            $query = $query->where(function($q) use ($params) {
                $q->where('stock', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('title', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('description', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('vin', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhereHas('floorplanVendor', function ($query) use ($params) {
                            $query->where('name', 'LIKE', '%' . $params['search_term'] . '%');
                        });
            });
        }

        if (isset($params['sort'])) {
            if ($params['sort'] === 'fp_vendor' || $params['sort'] === '-fp_vendor') {
                $direction = $params['sort'] === 'fp_vendor' ? 'DESC' : 'ASC';
                $query = $query->leftJoin('qb_vendors', 'qb_vendors.id', '=', 'inventory.fp_vendor')->orderBy('qb_vendors.name', $direction);
            } else {
                $query = $this->addSortQuery($query, $params['sort']);
            }
        }

        if ($paginated) {
            return $query->paginate($params['per_page'])->appends($params);
        }

        return $query->get();
    }

    /**
     * @param $params
     * @return Collection
     */
    public function getFloorplannedInventory($params)
    {
        $query = Inventory::select('*');
        
        $query->where([
            ['status', '<>', Inventory::STATUS_QUOTE],
            ['is_floorplan_bill', '=', 1],
            ['active', '=', 1],
            ['fp_vendor', '>', 0],
            ['true_cost', '>', 0],
            ['fp_balance', '>', 0]
        ])->whereNotNull('bill_id');
        
        if (isset($params['dealer_id'])) {
            $query = $query->where('inventory.dealer_id', $params['dealer_id']);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query = $query->where($params[self::CONDITION_AND_WHERE]);
        }
        
        if (isset($params['floorplan_vendor'])) {
            $query = $query->where('fp_vendor', $params['floorplan_vendor']);
        }
        
        if (isset($params['search_term'])) {
            $query = $query->where(function($q) use ($params) {
                $q->where('stock', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('title', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('description', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('vin', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhereHas('floorplanVendor', function ($query) use ($params) {
                            $query->where('name', 'LIKE', '%' . $params['search_term'] . '%');
                        });
            });
        }

        if (isset($params['sort'])) {
            if ($params['sort'] === 'fp_vendor' || $params['sort'] === '-fp_vendor') {
                $direction = $params['sort'] === 'fp_vendor' ? 'DESC' : 'ASC';
                $query = $query->leftJoin('qb_vendors', 'qb_vendors.id', '=', 'inventory.fp_vendor')->orderBy('qb_vendors.name', $direction);
            } else {
                $query = $this->addSortQuery($query, $params['sort']);
            }
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
