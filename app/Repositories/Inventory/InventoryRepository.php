<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\AttributeValue;
use App\Models\Inventory\File;
use App\Models\Inventory\Image;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryClapp;
use App\Models\Inventory\InventoryFeature;
use App\Models\Inventory\InventoryFile;
use App\Models\Inventory\InventoryImage;
use App\Traits\Repository\Transaction;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Traits\SortTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class InventoryRepository
 * @package App\Repositories\Inventory
 */
class InventoryRepository implements InventoryRepositoryInterface
{
    use SortTrait, Transaction;

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
     * @return Inventory
     */
    public function create($params)
    {
        $attributes = $params['attributes'] ?? [];
        $features = $params['features'] ?? [];
        $newImages = $params['new_images'] ?? [];
        $newFiles = $params['new_files'] ?? [];
        $clapps = $params['clapps'] ?? [];

        $attributeObjs = [];
        $featureObjs = [];
        $inventoryImageObjs = [];
        $inventoryFilesObjs = [];
        $clappObjs = [];

        unset($params['attributes']);
        unset($params['features']);
        unset($params['new_images']);
        unset($params['new_files']);
        unset($params['clapps']);

        foreach ($attributes as $attribute) {
            $attributeObjs[] = new AttributeValue($attribute);
        }

        foreach ($features as $feature) {
            $featureObjs[] = new InventoryFeature($feature);
        }

        foreach ($newImages as $newImage) {
            $imageObj = new Image($newImage);
            $imageObj->save();

            $inventoryImageObj = new InventoryImage($newImage);
            $inventoryImageObj->image_id = $imageObj->image_id;

            $inventoryImageObjs[] = $inventoryImageObj;
        }

        foreach ($newFiles as $newFile) {
            $fileObj = new File($newFile);
            $fileObj->save();

            $inventoryFileObj = new InventoryFile($newFile);
            $inventoryFileObj->file_id = $fileObj->id;

            $inventoryFilesObjs[] = $inventoryFileObj;
        }

        foreach (array_filter($clapps) as $field => $value) {
            $clappObjs[] = new InventoryClapp(['field' => $field, 'value' => $value]);
        }

        $item = new Inventory($params);

        $item->save();

        if (!empty($attributeObjs)) {
            $item->attributeValues()->saveMany($attributeObjs);
        }

        if (!empty($featureObjs)) {
            $item->inventoryFeatures()->saveMany($featureObjs);
        }

        if (!empty($inventoryImageObjs)) {
            $item->inventoryImages()->saveMany($inventoryImageObjs);
        }

        if (!empty($inventoryFilesObjs)) {
            $item->inventoryFiles()->saveMany($inventoryFilesObjs);
        }

        if (!empty($clappObjs)) {
            $item->clapps()->saveMany($clappObjs);
        }

        return $item;
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

    public function moveLocationId(int $from, int $to): int
    {
        return Inventory::where('dealer_location_id', $from)->update([
            'dealer_location_id' => $to
        ]);
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
     * @param array $params
     * @return bool
     */
    public function exists(array $params): bool
    {
        $query = Inventory::query();

        if (isset($params['dealer_id'])) {
            $query->where('dealer_id', '=', $params['dealer_id']);
        }

        if (isset($params['stock'])) {
            $query->where('stock', '=', $params['stock']);
        }

        // When Checking Stock on an EXISTING Item, Let's EXCLUDE the current item
        if (isset($params['inventory_id'])) {
            $query->where('inventory_id', '<>', $params['inventory_id']);
        }

        return $query->exists();
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
        /** @var Builder $query */
        $query = Inventory::select('*');

        if ($withDefault) {
            $query->where('status', '<>', Inventory::STATUS_QUOTE);
        }

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
}
