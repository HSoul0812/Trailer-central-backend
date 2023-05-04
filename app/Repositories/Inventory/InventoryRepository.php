<?php

namespace App\Repositories\Inventory;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\CRM\Dms\Quickbooks\BillCategory;
use App\Models\Inventory\AttributeValue;
use App\Models\Inventory\File;
use App\Models\Inventory\Image;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryClapp;
use App\Models\Inventory\InventoryFeature;
use App\Models\Inventory\InventoryFile;
use App\Models\Inventory\InventoryImage;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Traits\Repository\Transaction;
use App\Repositories\Traits\SortTrait;
use Dingo\Api\Exception\ResourceException;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Grimzy\LaravelMysqlSpatial\Eloquent\Builder as GrimzyBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;
use App\Models\User\User;
use App\Models\User\DealerLocation;

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

    private const DIMENSION_SEARCH_TERM_PATTERN = '/\d+\.?\d*\s*[\'|\"]/m';


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
        ],
        'archived_at' => [
            'field' => 'archived_at',
            'direction' => 'DESC'
        ],
        '-archived_at' => [
            'field' => 'archived_at',
            'direction' => 'ASC'
        ],
        'model' => [
            'field' => 'model',
            'direction' => 'DESC'
        ],
        '-model' => [
            'field' => 'model',
            'direction' => 'ASC'
        ]
    ];

    /**
     * @param array $params
     * @return Inventory
     * @throws \Exception
     */
    public function create($params): Inventory
    {
        $attributeObjs = $this->createAttributes($params['attributes'] ?? []);
        $featureObjs = $this->createFeatures($params['features'] ?? []);
        $clappObjs = $this->createClapps($params['clapps'] ?? []);

        $inventoryImageObjs = $this->createImages($params['new_images'] ?? []);
        $inventoryFilesObjs = $this->createFiles($params['new_files'] ?? []);

        // Set Geolocation if Not Exists
        if(empty($params['geolocation'])) {
            $params['geolocation'] = DB::raw('POINT(0, 0)');
        }

        // Unset Unneeded Params
        unset($params['attributes']);
        unset($params['features']);
        unset($params['new_images']);
        unset($params['new_files']);
        unset($params['clapps']);

        $item = new Inventory($params);

        // If there is an associated bill, we will set send_to_quickbooks to 1
        // so the cronjob can create the add bill approval record and also the
        // bill category record, allowing the dealer to update the bill later on
        $item->send_to_quickbooks = !empty($item->bill_id);

        // when the geolocation is not setup as Point, it will use a default Point
        if (empty($params['geolocation']) || !($params['geolocation'] instanceof Point)) {
            // Try to get the geolocation point class from the existing method and use it here
            $geolocation = $item->geolocationPoint();

            $item->geolocation = new Point($geolocation->latitude, $geolocation->longitude);
        }

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

        $this->handleFloorplanAndBill($item);

        return $item;
    }

    /**
     * @param array $params
     * @param array $options
     *
     * @return Inventory
     * @throws \Exception
     */
    public function update($params, array $options = []): Inventory
    {
        if (!isset($params['inventory_id'])) {
            throw new RepositoryInvalidArgumentException('inventory_id has been missed. Params - ' . json_encode($params));
        }

        /** @var Inventory $item */
        $item = Inventory::findOrFail($params['inventory_id']);

        // If there is an associated bill, we will set send_to_quickbooks to 1
        // so the cronjob can create the add bill approval record and also the
        // bill category record, allowing the dealer to update the bill later on
        $item->send_to_quickbooks = !empty($item->bill_id);

        // We'll note down this variable for now, we need this information, so we know
        // if we need to delete the bill approval record
        $firstTimeAttachBill = empty($item->bill_id) && !empty(data_get($params, 'bill_id'));

        $hasFloorplanInfo = !empty(data_get($params, 'true_cost'))
            && !empty(data_get($params, 'fp_vendor'))
            && !empty(data_get($params, 'fp_balance'));

        // We also note this down for now, we'll use it later
        $firstTimeAttachFloorplan = empty($item->is_floorplan_bill) && $hasFloorplanInfo;

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

        $item->fill($params);

        // If there is an associated bill, we will set send_to_quickbooks to 1
        // so the cronjob can create the add bill approval record and also the
        // bill category record, allowing the dealer to update the bill later on
        $item->send_to_quickbooks = !empty($item->bill_id);

        // when the geolocation is not setup as Point, it will use a Point which is build
        // either from inventory coordinates or dealer location coordinates as fallback
        if (empty($params['geolocation']) || !($params['geolocation'] instanceof Point)) {
            // Try to get the geolocation point class from the existing method and use it here
            $geolocation = $item->geolocationPoint();

            $item->geolocation = new Point($geolocation->latitude, $geolocation->longitude);
        }

        $item->save();

        // We only want to delete the bill approval record if this is the
        // first time that we attach the bill to this inventory
        $this->handleFloorplanAndBill($item, $firstTimeAttachBill || $firstTimeAttachFloorplan);

        $this->updateQbInvoiceItems($item);

        return $item;
    }

    /**
     * @fix this method has been source of mess because it is too general, it aims the developer to do not create
     *      another one specific for the new desired task
     *
     * @param array $params
     * @param array $queryParams
     * @return bool
     */
    public function massUpdate(array $params, array $queryParams = []): bool
    {
        if (!isset($params['dealer_id'])) {
            throw new RepositoryInvalidArgumentException('dealer_id has been missed. Params - ' . json_encode($params));
        }

        $dealerId = $params['dealer_id'];
        unset($params['dealer_id']); // to avoid update it

        Inventory::query()
            ->where('dealer_id', $dealerId)
            ->when(!empty($queryParams), function ($builder) use ($queryParams): void {
                /** @var GrimzyBuilder|EloquentBuilder $builder */
                $builder->where($queryParams);
            })
            ->update($params);

        return true;
    }

    /**
     * @param array $where
     * @param array $params
     * @return bool
     */
    public function bulkUpdate(array $where, array $params): bool
    {
        return Inventory::query()->where($where)->update($params);
    }

    /**
     * Update the qb_invoice_item_inventories table for sales person report updation.
     *
     * @param Inventory $item
     * @return void
     */
    private function updateQbInvoiceItems(Inventory $item)
    {
        $newTotalTrueCost = floatval($item->true_cost) + floatval($item->cost_of_shipping);
        $newTotalCost = floatval($item->cost_of_unit) + floatval($item->cost_of_shipping);
        $newFinalCost = 0;

        if ($item->pac_type === "percent") {
            $priceAdj = ($newTotalCost * floatval($item->pac_amount)) / 100;
            $newFinalCost = $newTotalCost + $priceAdj;
        } else {
            $newFinalCost = $newTotalCost + floatval($item->pac_amount);
        }

        DB::table('qb_invoice_item_inventories')
            ->where('inventory_id', '=', $item->id)
            ->update([
                'cost_overhead' => $newFinalCost,
                'true_total_cost' => $newTotalTrueCost
            ]);
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
        $query = Inventory::query()->select('*');

        if (isset($params['id'])) {
            $query->where('inventory_id', $params['id']);
        }

        if (isset($params['dealer_id'])) {
            $query->where('dealer_id', $params['dealer_id']);
        }

        if (isset($params['vin'])) {
            $query->where('vin', $params['vin']);
        }

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query->where($params[self::CONDITION_AND_WHERE]);
        }

        $include = (isset($params['include']) && is_string($params['include'])) ? explode(',', $params['include']) : [];

        if (in_array('attributes', $include)) {
            $query = $query->with('attributeValues.attribute');
        }

        if (in_array('features', $include)) {
            $query = $query->with('inventoryFeatures.featureList');
        }

        if (in_array('activeListings', $include)) {
            $query = $query->with('activeListings');
        }

        return $query->firstOrFail();
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

        DB::transaction(function () use (&$item, $params) {
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
    public function getAll($params, bool $withDefault = true, bool $paginated = false, $select = ['inventory.*'])
    {
        if ($paginated) {
            return $this->getPaginatedResults($params, $withDefault);
        }

        $query = $this->buildInventoryQuery($params, $withDefault, $select);

        return $query->get();
    }

    /**
     * @param Inventory $inventory
     * @param array $newImages
     * @return array
     */
    public function createInventoryImages(Inventory $inventory, array $newImages): array
    {
        $inventoryImageObjs = $this->createImages($newImages);
        $inventory->inventoryImages()->saveMany($inventoryImageObjs);

        return $inventoryImageObjs;
    }

    /**
     * @param Inventory $inventory
     * @param array $newFiles
     * @return array
     */
    public function createInventoryFiles(Inventory $inventory, array $newFiles): array
    {
        $inventoryFileObjs = $this->createFiles($newFiles);
        $inventory->inventoryFiles()->saveMany($inventoryFileObjs);

        return $inventoryFileObjs;
    }

    /**
     * Gets the query cursor to avoid memory leaks
     *
     * @param array $params
     * @return LazyCollection
     */
    public function getAllAsCursor(array $params): LazyCollection
    {
        return $this->buildInventoryQuery($params)->cursor();
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
     * Gets the query cursor to avoid memory leaks
     *
     * @param array $params
     * @return LazyCollection
     */
    public function getFloorplannedInventoryAsCursor(array $params): LazyCollection
    {
        return $this->getFloorplannedQuery($params)->cursor();
    }

    /**
     * @param $params
     * @return Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFloorplannedInventory($params, $paginate = true)
    {
        if ($paginate && !isset($params['per_page'])) {
            $params['per_page'] = 15;
        } else if (!$paginate && isset($params['per_page'])) {
            unset($params['per_page']);
        }

        $query = $this->getFloorplannedQuery($params);

        if ($paginate) {
            return $query->paginate($params['per_page'])->appends($params);
        }

        return $query->get();
    }

    private function getFloorplannedQuery(array $params): GrimzyBuilder
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

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query = $query->where($params[self::CONDITION_AND_WHERE]);
        }

        if (isset($params['floorplan_vendor'])) {
            $query = $query->where('fp_vendor', $params['floorplan_vendor']);
        }

        if (isset($params['search_term'])) {
            if (preg_match(self::DIMENSION_SEARCH_TERM_PATTERN, $params['search_term'])) {
                $params['search_term'] = floatval(trim($params['search_term'], ' \'"'));
                $query = $query->where(function ($q) use ($params) {
                    $q->where('length', $params['search_term'])
                        ->orWhere('width', $params['search_term'])
                        ->orWhere('height', $params['search_term'])
                        ->orWhere('length_inches', $params['search_term'])
                        ->orWhere('width_inches', $params['search_term'])
                        ->orWhere('height_inches', $params['search_term']);
                });
            } else {
                /**
                 * This converts strings like 4 Star Trailers to 4%Star%Trailers
                 * so it matches inventories with all words included in the search query
                 * with this, inventories with titles like `2023 4-Star Trailers dd BBQ Trailer`
                 * can be found with `Star BBQ Trailer` because Star%BBQ%Trailer would match.
                 */
                $params['search_term'] = preg_replace('/\s+/', '%', $params['search_term']);

                $query = $query->where(function ($q) use ($params) {
                    $q->where('stock', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('title', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('manufacturer', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('description', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('vin', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhereHas('floorplanVendor', function ($query) use ($params) {
                            $query->where('name', 'LIKE', '%' . $params['search_term'] . '%');
                        });
                });
            }
        }

        if (isset($params['sort'])) {
            if ($params['sort'] === 'fp_vendor' || $params['sort'] === '-fp_vendor') {
                $direction = $params['sort'] === 'fp_vendor' ? 'DESC' : 'ASC';
                $query = $query->leftJoin('qb_vendors', 'qb_vendors.id', '=', 'inventory.fp_vendor')->orderBy('qb_vendors.name', $direction);
            } else {
                $query = $this->addSortQuery($query, $params['sort']);
            }
        }

        return $query;
    }

    /**
     * @param int $dealer_id
     * @return \Illuminate\Database\Eloquent\Model|Builder|object|null
     */
    public function getPopularInventory(int $dealer_id)
    {
        return DB::table('inventory')
            ->select(DB::raw('count(*) as type_count, entity_type_id, category'))
            ->where('dealer_id', $dealer_id)
            ->groupBy('entity_type_id')
            ->orderBy('type_count', 'desc')
            ->first();
    }

    protected function getSortOrders()
    {
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
            ->select($select);

        if (isset($params['dealer_id'])) {
            // having this applied filter here will make faster the query
            $query = $query->where('inventory.dealer_id', $params['dealer_id']);
        }

        $query = $query->where('inventory.inventory_id', '>', 0);

        if (isset($params['include']) && is_string($params['include'])) {
            $query = $query->with(explode(',', $params['include']));
        }

        if (isset($params['is_archived'])) {
            $withDefault = false;
            $query = $query->where('inventory.is_archived', $params['is_archived']);
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

        if (isset($params['attribute_names']) && !$attributesEmpty) {
            $query = $query->join('eav_attribute_value', 'inventory.inventory_id', '=', 'eav_attribute_value.inventory_id')->orderBy('eav_attribute_value.attribute_id', 'desc');
            $query = $query->join('eav_attribute', 'eav_attribute.attribute_id', '=', 'eav_attribute_value.attribute_id');

            $query = $query->where(function ($q) use ($params) {
                foreach ($params['attribute_names'] as $attribute => $value) {
                    $q->orWhere(function ($q) use ($attribute, $value) {
                        $q->where('code', '=', $attribute)
                            ->where('value', '=', $value);
                    });
                }
            });
        }

        if (isset($params['status'])) {
            $query = $query->where('status', $params['status']);
        }

        if (!empty($params['exclude_status_ids'])) {
            $query->where(function (EloquentBuilder $query) use ($params) {
                $query
                    ->whereNotIn('status', Arr::wrap($params['exclude_status_ids']))
                    ->orWhereNull('status');
            });
        } else {
            // By default, we don't want to fetch the quote inventory
            // however, we'll keep fetching the inventory with status = null
            $query->where(function (EloquentBuilder $query) {
                $query
                    ->where('status', '!=', Inventory::STATUS_QUOTE)
                    ->orWhereNull('status');
            });
        }

        if (isset($params['condition'])) {
            $query = $query->where('condition', $params['condition']);
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

        if (isset($params['sold_at_lt'])) {
            $query = $query->where('inventory.sold_at', '<', $params['sold_at_lt']);
        }

        if (isset($params['integration_item_hash']) && $params['integration_item_hash'] === 'not_null') {
            $query = $query->whereNotNull('integration_item_hash');
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
            if(preg_match(self::DIMENSION_SEARCH_TERM_PATTERN, $params['search_term'])){
                $params['search_term'] = floatval(trim($params['search_term'],' \'"'));
                $query = $query->where(function ($q) use ($params) {
                    $q->where('length', $params['search_term'])
                        ->orWhere('width', $params['search_term'])
                        ->orWhere('height', $params['search_term'])
                        ->orWhere('length_inches', $params['search_term'])
                        ->orWhere('width_inches', $params['search_term'])
                        ->orWhere('height_inches', $params['search_term']);
                });
            }else{
                /**
                 * This converts strings like 4 Star Trailers to 4%Star%Trailers
                 * so it matches inventories with all words included in the search query
                 * with this, inventories with titles like `2023 4-Star Trailers dd BBQ Trailer`
                 * can be found with `Star BBQ Trailer` because Star%BBQ%Trailer would match.
                 */
                $params['search_term'] = preg_replace('/\s+/', '%', $params['search_term']);

                $query = $query->where(function ($q) use ($params) {
                    $q->where('stock', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('title', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('manufacturer', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('inventory.description', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('vin', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('price', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('model', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhereHas('floorplanVendor', function ($query) use ($params) {
                            $query->where('name', 'LIKE', '%' . $params['search_term'] . '%');
                        });
                });
            }
        }

        if (isset($params['images_greater_than'])) {
            $query->havingRaw('image_count >= '. $params['images_greater_than']);
        } elseif (isset($params['images_less_than'])) {
            $query->havingRaw('image_count <= '. $params['images_less_than']);
        } else {
            $query->select($select);
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

        if (isset($params['is_publishable_classified'])) {
            $query = $query->where('show_on_website', $params['is_publishable_classified']);
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
            if(empty($newImage['filename'])) {
                throw new ResourceException("Validation Failed", 'Filename cant be blank');
            }
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
            if (!empty($feature['feature_list_id'])) {
                $featureObjs[] = new InventoryFeature($feature);
            }
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
        $inventory->timestamps = false;
        //we need to disable the events so the observer method doesn't trigger
        $inventory->saveQuietly();
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

    /**
     * This method will handle the creation of floorplan and bill data
     * we will let the crm project handle the actual approval creation
     * for this project, we'll just prepare the data so the the InventorySync
     * command can do its job properly
     *
     * @param Inventory $inventory
     * @param bool $deleteBillApproval Set to true to delete the bill approval
     * @return void
     */
    private function handleFloorplanAndBill(Inventory $inventory, bool $deleteBillApproval = true): void
    {
        if (empty($inventory->bill_id)) {
            return;
        }

        // 1. In the create inventory case, we always want to delete the bill
        // approval record if the inventory has the bill attached to it
        // 2. In the update inventory case, we only want to delete the bill
        // approval record if the inventory has the bill attached for the first time
        // we do this because more than one inventory can use the same bill, if we don't do this
        // then the cronjob won't create a new bill approval record
        if ($deleteBillApproval) {
            resolve(QuickbookApprovalRepositoryInterface::class)->deleteByTbPrimaryId($inventory->bill_id, Bill::getTableName(), $inventory->dealer_id);
        }

        // We only want to process floorplan data if the inventory
        // is not a floorplan bill yet
        if ($inventory->is_floorplan_bill) {
            return;
        }

        $shouldAddFloorplanData = !empty($inventory->true_cost)
            && !empty($inventory->fp_vendor)
            && !empty($inventory->fp_balance);

        if (!$shouldAddFloorplanData) {
            return;
        }

        // This will make sure the tc-crm cron can capture this inventory
        // and try to create new bill and bill payment approval records
        $inventory->qb_sync_processed = false;
        $inventory->is_floorplan_bill = true;
        $inventory->save();

        // Delete all the bill categories for this bill if we add a floorplan
        // payment to this inventory, the behavior is the same with the old UI
        BillCategory::query()
            ->where('bill_id', $inventory->bill_id)
            ->delete();

        $inventory->bill()->update([
            'status' => Bill::STATUS_PAID,
        ]);
    }


    /**
     * @param int $dealerId
     * @param array $inventoryParams
     * @param null $deletedAt
     * @return int
     * @throws \Exception
     */
    public function massUpdateDealerInventoryOnActiveStateChange(int $dealerId, array $inventoryParams, $deletedAt): int
    {
        if ($inventoryParams['active'] && is_null($deletedAt)) {
            throw new \Exception('Deleted at is required when activating dealer inventories.');
        }

        $archivedAt = $inventoryParams['active'] ? $deletedAt : null;

        $queryParams = [
            ['active', !$inventoryParams['active']],
            ['archived_at', $archivedAt]
        ];

        return $this->massUpdate($inventoryParams, $queryParams);
    }

    /**
     * Find the inventory by stock
     *
     * @param int $dealerId
     * @param string $stock
     * @return Inventory|null
     */
    public function findByStock(int $dealerId, string $stock): ?Inventory
    {
        return Inventory::where('dealer_id', $dealerId)->where('stock', $stock)->first();
    }

    /**
     * Get necessary configuration to generate overlays
     *
     * @param  int  $inventoryId
     * @return array{
     *     dealer_id:int,
     *     inventory_id: int,
     *     overlay_logo: string,
     *     overlay_logo_position: string,
     *     overlay_logo_width: int,
     *     overlay_upper: string,
     *     overlay_upper_bg: string,
     *     overlay_upper_alpha: string,
     *     overlay_upper_text: string,
     *     overlay_upper_size: int,
     *     overlay_upper_margin: string,
     *     overlay_lower: string,
     *     overlay_lower_bg: string,
     *     overlay_lower_alpha: string,
     *     overlay_lower_text: string,
     *     overlay_lower_size: int,
     *     overlay_lower_margin: string,
     *     overlay_default: int,
     *     overlay_enabled: int,
     *     dealer_overlay_enabled: int,
     *     overlay_text_dealer: string,
     *     overlay_text_phone: string,
     *     country: string,
     *     overlay_text_location: string,
     *     overlay_updated_at: string
     *     }
     */
    public function getOverlayParams(int $inventoryId): array
    {
        $userTableName = User::getTableName();
        $dealerLocationTable = DealerLocation::getTableName();
        $inventoryTable = Inventory::getTableName();

        $columns = [
            $userTableName.'.dealer_id',
            'inventory_id',
            'overlay_logo',
            'overlay_logo_position',
            'overlay_logo_width',
            'overlay_logo_height',
            'overlay_upper',
            'overlay_upper_bg',
            'overlay_upper_alpha',
            'overlay_upper_text',
            'overlay_upper_size',
            'overlay_upper_margin',
            'overlay_lower',
            'overlay_lower_bg',
            'overlay_lower_alpha',
            'overlay_lower_text',
            'overlay_lower_size',
            'overlay_lower_margin',
            'overlay_default',
            $inventoryTable.'.overlay_enabled',
            $userTableName.'.overlay_enabled AS dealer_overlay_enabled',
            $userTableName.'.name AS overlay_text_dealer',
            $dealerLocationTable.'.phone AS overlay_text_phone',
            $dealerLocationTable.'.country',
            $userTableName.'.overlay_updated_at',
            DB::raw(sprintf("CONCAT(%s.city,', ',%s.region) AS overlay_text_location", $dealerLocationTable, $dealerLocationTable))
        ];

        $query = Inventory::select($columns)
                        ->leftJoin($userTableName, $inventoryTable .'.dealer_id', '=', $userTableName .'.dealer_id')
                        ->leftJoin($dealerLocationTable, $inventoryTable .'.dealer_location_id', '=', $dealerLocationTable .'.dealer_location_id')
                        ->where($inventoryTable .'.inventory_id', $inventoryId);

        $overlayParams = $query->first()->toArray();

        if (isset($overlayParams['overlay_text_phone'])) {
            $overlayParams['overlay_text_phone'] = DealerLocation::phoneWithNationalFormat(
                $overlayParams['overlay_text_phone'],
                $overlayParams['country']
            );

            unset($overlayParams['country']);
        }

        return $overlayParams;
    }

    /**
     * @param  int  $inventoryId
     * @return \Illuminate\Database\Eloquent\Collection<InventoryImage>|InventoryImage[] all images related to the inventory
     */
    public function getInventoryImages(int $inventoryId): \Illuminate\Database\Eloquent\Collection
    {
        $inventory = $this->get(['id' => $inventoryId]);

        return $inventory->inventoryImages()->get();
    }

    /**
     * @return bool true when it changed desired image, false when it di not
     */
    public function markImageAsOverlayGenerated(int $imageId): bool
    {
        return (bool) InventoryImage::query()
            ->where('image_id', $imageId)
            ->update(['overlay_updated_at' => now()]);
    }

    public function getInventoryByDealerIdWhichShouldHaveImageOverlayButTheyDoesNot(int $dealerId): LazyCollection
    {
        $IS_DEFAULT_IMAGE = InventoryImage::IS_DEFAULT;
        $OVERLAY_FOR_ALL_IMAGES = Inventory::OVERLAY_ENABLED_ALL;
        $OVERLAY_FOR_PRIMARY_IMAGE = Inventory::OVERLAY_ENABLED_PRIMARY;

        $subQueryAllImages = <<<SQL
                    SELECT count(image.image_id)
                    FROM image
                          JOIN inventory_image ON inventory_image.image_id = image.image_id
                    WHERE inventory_image.inventory_id = inventory.inventory_id
                    AND inventory.overlay_enabled = {$OVERLAY_FOR_ALL_IMAGES}
                    AND filename_with_overlay IS NULL
SQL;

        $subQueryPrimaryImage = <<<SQL
                    SELECT count(image.image_id)
                    FROM image
                           JOIN inventory_image ON inventory_image.image_id = image.image_id
                    WHERE inventory_image.inventory_id = inventory.inventory_id AND
                          (inventory_image.is_default = {$IS_DEFAULT_IMAGE} OR inventory_image.position = 0 OR inventory_image.position IS NULL)
                    AND inventory.overlay_enabled = {$OVERLAY_FOR_PRIMARY_IMAGE}
                    AND filename_with_overlay IS NULL
SQL;

        // This query is a good enough approximation, we it is not accurate, it will try to generate overlay any-case
        // but the job will figure out the image should not have an overlay
        // @todo we will need to ensure all primary images has is_default as 1 (Bulk uploader is setting it wrongly up)

        return Inventory::query()
            ->select('inventory.*')
            ->join('dealer', 'dealer.dealer_id', '=', 'inventory.dealer_id')
            ->join('inventory_image', 'inventory_image.inventory_id', '=', 'inventory.inventory_id')
            ->join('image', 'image.image_id', '=', 'inventory_image.image_id')
            ->where('dealer.dealer_id', $dealerId)
            ->where('inventory.overlay_enabled', '>=', Inventory::OVERLAY_ENABLED_PRIMARY)
            ->whereRaw("(({$subQueryAllImages}) > 0 OR ({$subQueryPrimaryImage}) > 0)")
            ->groupBy('inventory.inventory_id')
            ->cursor();
    }
}
