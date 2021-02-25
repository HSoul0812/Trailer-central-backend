<?php

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Inventory;
use App\Repositories\Traits\SortTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Grimzy\LaravelMysqlSpatial\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Class InventoryRepository
 * @package App\Repositories\Inventory
 */
class InventoryRepository implements InventoryRepositoryInterface
{
    private const DEFAULT_PAGE_SIZE = 15;
    
    private const SHOW_UNITS_WITH_TRUE_COST = 1;
    private const DO_NOT_SHOW_UNITS_WITH_TRUE_COST = 0;
    
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
            $item->inventoryFeatures()->delete();
            $item->clapps()->delete();
            $item->lotVantageInventory()->delete();

            if (isset($params['imageIds']) && is_array($params['imageIds'])) {
                $item->images()->whereIn('image.image_id', $params['imageIds'])->delete();
            }

            if (isset($params['fileIds']) && is_array($params['fileIds'])) {
                $item->files()->whereIn('file.id', $params['fileIds'])->delete();
            }

            $item->delete();
        });

        return true;
    }

    /**
     * @param $params
     * @param bool $withDefault
     * @param bool $paginated
     * @return Collection|LengthAwarePaginator
     */
    public function getAll($params, bool $withDefault = true, bool $paginated = false)
    {       
        if ($paginated) {
            return $this->getPaginatedResults($params, $withDefault);
        }
        
        $query = $this->buildInventoryQuery($params, $withDefault);

        return $query->get();
    }

    /**
     * @param $params
     * @param bool $withDefault
     * @return Collection|LengthAwarePaginator
     */
    public function getAllWithHavingCount($params, bool $withDefault = true)
    {
        $select = $params[self::SELECT] ? implode(',', $params[self::SELECT]) : '*';

        /** @var Builder $query */
        $query = Inventory::select($select);

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query = $query->where($params[self::CONDITION_AND_WHERE]);
        }

        $havingCount = $params[self::CONDITION_AND_HAVING_COUNT];

        $query = $query->having(DB::raw('count(' . $havingCount[0] . ')'), $havingCount[1], $havingCount[2]);

        if (isset($params[self::GROUP_BY])) {
            $query = $query->groupBy($params[self::GROUP_BY]);
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
    
    /**
     * @param array $params
     * @param bool $withDefault whether to apply default conditions or not
     * 
     * @return Builder
     */
    private function buildInventoryQuery(array $params, bool $withDefault = true) : Builder
    {
        /** @var Builder $query */
        $query = Inventory::where('inventory.inventory_id', '>', 0);
        
        if ($withDefault) {
            $query->where('status', '<>', Inventory::STATUS_QUOTE);
        }
        
        if (isset($params['condition'])) {
            $query = $query->where('condition', $params['condition']);
        }

        if (isset($params['dealer_id'])) {
            $query = $query->where('inventory.dealer_id', $params['dealer_id']);
        }
        
        if (isset($params['dealer_location_id'])) {
            $query = $query->where('inventory.dealer_location_id', $params['dealer_location_id']);
        }
        
        if (isset($params['units_with_true_cost'])) {
            if ($params['units_with_true_cost'] == self::SHOW_UNITS_WITH_TRUE_COST) {
                $query = $query->where('true_cost', '>', 0);
            } else if ($params['units_with_true_cost'] == self::DO_NOT_SHOW_UNITS_WITH_TRUE_COST) {
                $query = $query->where('true_cost', 0);
            }            
        }
        
        if ($withDefault) {
            $query = $query->where(self::DEFAULT_GET_PARAMS[self::CONDITION_AND_WHERE]);
        }

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query = $query->where($params[self::CONDITION_AND_WHERE]);
        }

        if (isset($params[self::CONDITION_AND_WHERE_IN]) && is_array($params[self::CONDITION_AND_WHERE_IN])) {
            foreach ($params[self::CONDITION_AND_WHERE_IN] as $field => $values) {
                $query = $query->whereIn($field, $values);
            }
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
        
        if (isset($params['images_greater_than'])) {
            $query->havingRaw('image_count >= '. $params['images_greater_than']);
        } else if (isset($params['images_less_than'])) {
            $query->havingRaw('image_count <= '. $params['images_less_than']);
        } else {
            $query->select('*');
        }

        if (isset($params['sort'])) {
            if ($params['sort'] === 'fp_vendor' || $params['sort'] === '-fp_vendor') {
                $direction = $params['sort'] === 'fp_vendor' ? 'DESC' : 'ASC';
                $query = $query->leftJoin('qb_vendors', 'qb_vendors.id', '=', 'inventory.fp_vendor')->orderBy('qb_vendors.name', $direction);
            } else {
                $query = $this->addSortQuery($query, $params['sort']);
            }
        }
        
        if (isset($params['images_greater_than']) || isset($params['images_less_than'])) {
            $query = $query->leftJoin('inventory_image', 'inventory_image.inventory_id', '=', 'inventory.inventory_id');
            $query->selectRaw('inventory.*, count(inventory_image.inventory_id) as image_count');
            $query->groupBy('inventory.inventory_id');
                  
        }

        return $query;
    }
    
    private function getResultsCountFromQuery(Builder $query) : int
    {
        $queryString = str_replace(array('?'), array('\'%s\''), $query->toSql());
        $queryString = vsprintf($queryString, $query->getBindings());
        return current(DB::select(DB::raw("SELECT count(*) as row_count FROM ($queryString) as inventory_count")))->row_count;
    }
    
    private function getPaginatedResults($params, bool $withDefault = true) 
    {
        $perPage = !isset($params['per_page']) ? self::DEFAULT_PAGE_SIZE : (int)$params['per_page'];
        $currentPage = !isset($params['page']) ? 1 : (int)$params['page'];
        
        $paginatedQuery = $this->buildInventoryQuery($params, $withDefault);
        $resultsCount = $this->getResultsCountFromQuery($paginatedQuery);
        
        $paginatedQuery->skip(($currentPage - 1) * $perPage);
        $paginatedQuery->take($perPage);
        
        return (new LengthAwarePaginator(
            $paginatedQuery->get(), 
            $resultsCount, 
            $perPage, 
            $currentPage, 
            ["path" => URL::to('/')."/api/inventory"]
        ))->appends($params);
    }
}
