<?php

namespace App\Repositories\Inventory\Packages;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Inventory\Packages\Package;
use App\Traits\Repository\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class PackageRepository
 * @package App\Repositories\Inventory\Packages
 */
class PackageRepository implements PackageRepositoryInterface
{
    use Transaction;

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
        $query = $query->with('inventories');

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        return $query->paginate($params['per_page'])->appends($params);
    }
}
