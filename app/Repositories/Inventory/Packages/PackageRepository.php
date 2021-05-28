<?php

namespace App\Repositories\Inventory\Packages;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Inventory\Packages\Package;
use App\Repositories\Traits\SortTrait;
use App\Traits\Repository\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class PackageRepository
 * @package App\Repositories\Inventory\Packages
 */
class PackageRepository implements PackageRepositoryInterface
{
    use Transaction, SortTrait;

    private $sortOrders = [
        'id' => [
            'field' => 'id',
            'direction' => 'DESC'
        ],
        '-id' => [
            'field' => 'id',
            'direction' => 'ASC'
        ],
        'visible_with_main_item' => [
            'field' => 'visible_with_main_item',
            'direction' => 'DESC'
        ],
        '-visible_with_main_item' => [
            'field' => 'visible_with_main_item',
            'direction' => 'ASC'
        ],
    ];

    /**
     * @param $params
     * @return Package
     */
    public function create($params): Package
    {
        $package = new Package();
        $inventories = [];

        if (!empty($params['inventories'])) {
            $inventories = $params['inventories'];
            unset($params['inventories']);
        }

        $package->fill($params)->save();

        if (!is_array($inventories)) {
            throw new RepositoryInvalidArgumentException('inventories param must be array. Params - ' . json_encode($params));
        }

        $inventories = array_column($inventories, null, 'inventory_id');

        if (!empty($inventories)) {
            $package->inventories()->sync($inventories);
        }

        return $package;
    }

    public function update($params): Package
    {
        if (!isset($params['id'])) {
            throw new RepositoryInvalidArgumentException('id has been missed. Params - ' . json_encode($params));
        }

        /** @var Package $package */
        $package = Package::findOrFail($params['id']);
        $inventories = [];

        if (!empty($params['inventories'])) {
            $inventories = $params['inventories'];
            unset($params['inventories']);
        }

        $package->fill($params)->save();

        if (!is_array($inventories)) {
            throw new RepositoryInvalidArgumentException('inventories param must be array. Params - ' . json_encode($params));
        }

        $package->inventories()->detach();
        $inventories = array_column($inventories, null, 'inventory_id');

        if (!empty($inventories)) {
            $package->inventories()->sync($inventories);
        }

        return $package;
    }

    /**
     * @param array $params
     * @return Package|null
     */
    public function get($params): ?Package
    {
        $query = Package::query();

        if (!isset($params['id'])) {
            throw new RepositoryInvalidArgumentException('id has been missed. Params - ' . json_encode($params));
        }

        $query = $query->where(['id' => $params['id']]);

        if (isset($params['dealer_id'])) {
            $query = $query->where(['dealer_id' => $params['dealer_id']]);
        }

        return $query->first();
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function delete($params): bool
    {
        /** @var Package $package */
        $package = Package::findOrFail($params['id']);

        return (bool)$package->delete();
    }

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getAll($params): LengthAwarePaginator
    {
        $query = Package::query();

        if (!isset($params['dealer_id'])) {
            throw new RepositoryInvalidArgumentException('dealer_id has been missed. Params - ' . json_encode($params));
        }

        $query = $query->where(['dealer_id' => $params['dealer_id']]);

        /**
         * @todo Move it to event (after update inventory item)
         * If the main inventory in package is not available, do not show this package
         */
        $query->whereHas('packagesInventory', function($query) {
            $query->where(function ($query) {
                $query->where('is_main_item', '=', 1)
                    ->whereHas('inventory', function ($query) {
                        $query->where('status', '=', 1);
                    });
            });
        });

        $query = $query->with(['inventories' => function ($query) use ($params) {
            $query->where('status', '=', 1);
        }]);

        if (isset($params['visible_with_main_item'])) {
            $query = $query->where(['visible_with_main_item' => $params['visible_with_main_item']]);
        }

        if (isset($params['inventory_id']) && isset($params['is_main_item'])) {
            $query->whereHas('packagesInventory', function($query) use ($params) {
                $query->where('inventory_id', $params['inventory_id']);
                $query->where('is_main_item', $params['is_main_item']);
            });
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * {@inheritDoc}
     */
    protected function getSortOrders(): array
    {
        return $this->sortOrders;
    }
}
