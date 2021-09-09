<?php

declare(strict_types=1);

namespace App\Repositories\Inventory\Floorplan;

use App\Models\Inventory\Inventory;
use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Floorplan\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class VendorRepository implements VendorRepositoryInterface
{
    public function create($params): Vendor
    {
        throw new NotImplementedException;
    }

    public function delete($params): bool
    {
        throw new NotImplementedException;
    }

    public function get($params): Vendor
    {
        throw new NotImplementedException;
    }

    public function getAll($params): LengthAwarePaginator
    {
        $query = Vendor::select('*');

        if (isset($params['dealer_id'])) {
            $query->where(static function (EloquentBuilder $query) use ($params): void {
                $query->where(static function (EloquentBuilder $query) use ($params): void {
                    $query->where('show_on_floorplan', 1)->where('dealer_id', $params['dealer_id']);
                });

                $query->orWhereIn('id', static function (Builder $query) use ($params): void {
                    $query->select('fp_vendor')
                        ->from(Inventory::getTableName())
                        ->where([
                            ['status', '<>', Inventory::STATUS_QUOTE],
                            ['is_floorplan_bill', '=', 1],
                            ['active', '=', 1],
                            ['fp_vendor', '>', 0],
                            ['true_cost', '>', 0],
                            ['fp_balance', '>', 0]
                        ])
                        ->whereNotNull('bill_id')
                        ->where('inventory.dealer_id', $params['dealer_id'])
                        ->groupBy('fp_vendor');
                });
            });
        } else {
            $query->where('show_on_floorplan', 1);
        }

        if (isset($params['search_term'])) {
            $query->where('name', 'like', '%'.$params['search_term'].'%');
        }
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        // Do not show deleted vendors
        $query = $query->whereNull('deleted_at');

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params): bool
    {
        throw new NotImplementedException;
    }
}
