<?php

namespace App\Repositories\Inventory;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Inventory\AttributeValue;
use App\Models\Inventory\File;
use App\Models\Inventory\Image;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryClapp;
use App\Models\Inventory\InventoryFeature;
use App\Models\Inventory\InventoryFile;
use App\Models\Inventory\InventoryImage;
use App\Traits\Repository\Transaction;
use App\Repositories\Traits\SortTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Grimzy\LaravelMysqlSpatial\Eloquent\Builder as GrimzyBuilder;

/**
 * Class InventoryRepository
 * @package App\Repositories\Inventory
 */
class InventoryRepository implements InventoryRepositoryInterface
{
    use SortTrait, Transaction;

    private const DEFAULT_PAGE_SIZE = 15;

    private const SHOW_UNITS_WITH_TRUE_COST = 1;
    private const DO_NOT_SHOW_UNITS_WITH_TRUE_COST = 0;


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
     * @param array $params
     * @return Inventory
     */
    public function create($params): Inventory
    {
        $attributeObjs = $this->createAttributes($params['attributes'] ?? []);
        $featureObjs = $this->createFeatures($params['features'] ?? []);
        $clappObjs = $this->createClapps($params['clapps'] ?? []);

        $inventoryImageObjs = $this->createImages($params['new_images'] ?? []);
        $inventoryFilesObjs = $this->createFiles($params['new_files'] ?? []);

        unset($params['attributes']);
        unset($params['features']);
        unset($params['new_images']);
        unset($params['new_files']);
        unset($params['clapps']);

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
     * @param array $params
     * @param array $options
     *
     * @return Inventory
     */
    public function update($params, array $options = []): Inventory
    {
        if (!isset($params['inventory_id'])) {
            throw new RepositoryInvalidArgumentException('inventory_id has been missed. Params - ' . json_encode($params));
        }

        /** @var Inventory $item */
        $item = Inventory::findOrFail($params['inventory_id']);

        $inventoryImageObjs = $this->createImages($params['new_images'] ?? []);

        if (!empty($inventoryImageObjs)) {
            $item->inventoryImages()->saveMany($inventoryImageObjs);
        }

        $this->updateImages($item, $params['existing_images'] ?? []);

        if (!empty($params['images_to_delete'])) {
            $item->images()->whereIn('image.image_id', array_column($params['images_to_delete'], 'image_id'))->delete();
        }

        $inventoryFilesObjs = $this->createFiles($params['new_files'] ?? []);

        if (!empty($inventoryFilesObjs)) {
            $item->inventoryFiles()->saveMany($inventoryFilesObjs);
        }

        $this->updateFiles($item, $params['existing_files'] ?? []);

        if (!empty($params['files_to_delete'])) {
            $item->files()->whereIn('file.id', array_column($params['files_to_delete'], 'file_id'))->delete();
        }

        if ($options['updateAttributes'] ?? false) {
            $item->attributeValues()->delete();

            $attributeObjs = $this->createAttributes($params['attributes'] ?? []);

            if (!empty($attributeObjs)) {
                $item->attributeValues()->saveMany($attributeObjs);
            }
        }

        if ($options['updateFeatures'] ?? false) {
            $item->inventoryFeatures()->delete();

            $featureObjs = $this->createFeatures($params['features'] ?? []);

            if (!empty($featureObjs)) {
                $item->inventoryFeatures()->saveMany($featureObjs);
            }
        }

        if ($options['updateClapps'] ?? false) {
            $item->clapps()->delete();

            $clappObjs = $this->createClapps($params['clapps'] ?? []);

            if (!empty($clappObjs)) {
                $item->clapps()->saveMany($clappObjs);
            }
        }

        unset($params['attributes']);
        unset($params['features']);
        unset($params['new_images']);
        unset($params['existing_images']);
        unset($params['images_to_delete']);
        unset($params['new_files']);
        unset($params['existing_files']);
        unset($params['files_to_delete']);
        unset($params['clapps']);

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

        $query->where('status', '!=', Inventory::STATUS_QUOTE);

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

        return false;
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
            ['is_floorplan_bill', '=', 1],
            ['active', '=', 1],
            ['fp_vendor', '>', 0],
            ['true_cost', '>', 0],
            ['fp_balance', '>', 0]
        ])->whereNotNull('bill_id')->whereNotNull('status');

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
    private function buildInventoryQuery(array $params, bool $withDefault = true) : GrimzyBuilder
    {
        /** @var Builder $query */
        $query = Inventory::where('inventory.inventory_id', '>', 0);

        if ($withDefault) {
            $query->where('status', '<>', Inventory::STATUS_QUOTE);
        }

        if (isset($params['status'])) {
            $query = $query->where('status', $params['status']);
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

        if (isset($params['inventory_ids']) && is_array($params['inventory_ids'])) {
            $query = $query->whereIn('inventory.inventory_id', $params['inventory_ids']);
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

        if (isset($params['is_archived'])) {
            $query = $query->where('inventory.is_archived', $params['is_archived']);
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

    private function getResultsCountFromQuery(GrimzyBuilder $query) : int
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

    /**
     * @param array $newImages
     * @return InventoryImage[]
     */
    private function createImages(array $newImages): array
    {
        $inventoryImageObjs = [];

        foreach ($newImages as $newImage) {
            $imageObj = new Image($newImage);
            $imageObj->save();

            $inventoryImageObj = new InventoryImage($newImage);
            $inventoryImageObj->image_id = $imageObj->image_id;

            $inventoryImageObjs[] = $inventoryImageObj;
        }

        return $inventoryImageObjs;
    }

    /**
     * @param array $newFiles
     * @return InventoryFile[]
     */
    private function createFiles(array $newFiles): array
    {
        $inventoryFilesObjs = [];

        foreach ($newFiles as $newFile) {
            $fileObj = new File($newFile);
            $fileObj->save();

            $inventoryFileObj = new InventoryFile($newFile);
            $inventoryFileObj->file_id = $fileObj->id;

            $inventoryFilesObjs[] = $inventoryFileObj;
        }

        return $inventoryFilesObjs;
    }

    /**
     * @param array $attributes
     * @return AttributeValue[]
     */
    private function createAttributes(array $attributes): array
    {
        $attributeObjs = [];

        foreach ($attributes as $attribute) {
            $attributeObjs[] = new AttributeValue($attribute);
        }

        return $attributeObjs;
    }

    /**
     * @param array $features
     * @return InventoryFeature[]
     */
    private function createFeatures(array $features): array
    {
        $featureObjs = [];

        foreach ($features as $feature) {
            $featureObjs[] = new InventoryFeature($feature);
        }

        return $featureObjs;
    }

    /**
     * @param array $clapps
     * @return InventoryClapp[]
     */
    private function createClapps(array $clapps): array
    {
        $clappObjs = [];

        foreach (array_filter($clapps) as $field => $value) {
            $clappObjs[] = new InventoryClapp(['field' => $field, 'value' => $value]);
        }

        return $clappObjs;
    }

    /**
     * @param Inventory $item
     * @param array $images
     */
    private function updateImages(Inventory $item, array $images)
    {
        foreach ($images as $existingImage) {
            if (!isset($existingImage['image_id'])) {
                continue;
            }

            $item->inventoryImages()->where('image_id', '=', $existingImage['image_id'])->update($existingImage);
        }
    }

    /**
     * @param Inventory $item
     * @param array $existingFiles
     */
    private function updateFiles(Inventory $item, array $existingFiles)
    {
        foreach ($existingFiles ?? [] as $existingFile) {
            if (!isset($existingFile['file_id'])) {
                continue;
            }

            $fileFields = with(new File())->getFillable();
            $fileParams = array_intersect_key($existingFile, array_combine($fileFields, array_fill(0, count($fileFields), 0)));

            $inventoryFileFields = with(new InventoryFile())->getFillable();
            $inventoryFileParams = array_intersect_key($existingFile, array_combine($inventoryFileFields, array_fill(0, count($inventoryFileFields), 0)));

            $item->files()->where('file.id', '=', $existingFile['file_id'])->update($fileParams);
            $item->inventoryFiles()->where('file_id', '=', $existingFile['file_id'])->update($inventoryFileParams);
        }
    }
}
