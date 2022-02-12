<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Inventory\Inventory;
use App\Models\Marketing\Craigslist\ActivePost;
use App\Repositories\Traits\SortTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class InventoryRepository
 * @package App\Repositories\Marketing\Craigslist
 */
class InventoryRepository implements InventoryRepositoryInterface
{
    use SortTrait, Transaction;

    private const DEFAULT_PAGE_SIZE = 15;


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
        'stock' => [
            'field' => 'stock',
            'direction' => 'DESC'
        ],
        '-stock' => [
            'field' => 'stock',
            'direction' => 'ASC'
        ],
        'category' => [
            'field' => 'category',
            'direction' => 'DESC'
        ],
        '-category' => [
            'field' => 'category',
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
        'sales_price' => [
            'field' => 'sales_price',
            'direction' => 'DESC'
        ],
        '-sales_price' => [
            'field' => 'sales_price',
            'direction' => 'ASC'
        ],
        'status' => [
            'field' => 'status',
            'direction' => 'DESC'
        ],
        '-status' => [
            'field' => 'status',
            'direction' => 'ASC'
        ]
    ];

    /**
     * @param array $params
     * @throws NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @param array $options
     *
     * @throws NotImplementedException
     */
    public function update($params, array $options = [])
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * 
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * 
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
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

    protected function getSortOrders() {
        return $this->sortOrders;
    }

    /**
     * @param array $params
     * @param bool $withDefault whether to apply default conditions or not
     *
     * @return Builder
     */
    private function buildInventoryQuery(
        array $params,
        bool $withDefault = true,
        array $select = ['inventory.*']
    ) : GrimzyBuilder {
        /** @var Builder $query */
        $query = Inventory::query()
            ->select($select)
            ->crossJoin(ActivePost::getTableName(), ActivePost::getTableName().'.inventory_id',
                    '=', Inventory::getTableName().'.inventory_id')
            ->crossJoin(Profile::getTableName(), Profile::getTableName().'.profile_id',
                    '=', ActivePost::getTableName().'.profile_id')
            ->where(function($query) use($params) {
                $query->where(Inventory::getTableName().'.dealer_id', '=', $params['dealer_id'])
                      ->orWhere(Profile::getTableName().'.dealer_id', '=', $params['dealer_id']);
            });

        if (isset($params['include']) && is_string($params['include'])) {
            $query = $query->with(explode(',', $params['include']));
        }

        $attributesEmpty = true;

        if (isset($params['attribute_names'])) {
            foreach ($params['attribute_names'] as $value) {
                if (!empty($value)) {
                    $attributesEmpty = false;
                    break;
                }
            }
        }

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

        if (isset($params['is_archived'])) {
            $withDefault = false;
            $query = $query->where('inventory.is_archived', $params['is_archived']);
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

        if (isset($params['search_term'])) {
            $query = $query->where(function ($q) use ($params) {
                $q->where('stock', 'LIKE', '%' . $params['search_term'] . '%')
                  ->orWhere('title', 'LIKE', '%' . $params['search_term'] . '%')
                  ->orWhere('inventory.description', 'LIKE', '%' . $params['search_term'] . '%')
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

        if (isset($params['images_greater_than']) || isset($params['images_less_than'])) {
            $query = $query->leftJoin('inventory_image', 'inventory_image.inventory_id', '=', 'inventory.inventory_id');
            $query->selectRaw('count(inventory_image.inventory_id) as image_count');
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
            if (!is_array($attribute)) {
                continue;
            }
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

            $inventoryImageFields = with(new InventoryImage())->getFillable();
            $inventoryImageParams = array_intersect_key($existingImage, array_combine($inventoryImageFields, array_fill(0, count($inventoryImageFields), 0)));

            $item->inventoryImages()->where('image_id', '=', $existingImage['image_id'])->update($inventoryImageParams);
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

    /**
     * {@inheritDoc}
     */
    public function getAndIncrementTimesViewed(array $params): Inventory
    {
        $inventory = $this->get($params);
        $inventory->times_viewed += 1;
        $inventory->save();
        return $inventory;
    }

    /**
     * @param int $dealerId
     *
     * @return Collection
     */
    public function getTitles(int $dealerId): Collection
    {
        $params = [
            'dealer_id' => $dealerId,
        ];

        $query = $this->buildInventoryQuery($params, false, ['inventory_id', 'title', 'vin']);

        return $query->get();
    }
}
