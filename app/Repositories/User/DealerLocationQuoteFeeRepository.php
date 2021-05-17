<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User\DealerLocation;
use App\Models\User\DealerLocationQuoteFee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DealerLocationQuoteFeeRepository implements DealerLocationQuoteFeeRepositoryInterface
{
    /**
     * Retrieves all the quote fees.
     *
     * if there is not a provided dealer_location_id, it will group by fee_type
     *
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getAll(array $params): LengthAwarePaginator
    {
        $quoteFeeTableName = DealerLocationQuoteFee::getTableName();
        $dealerLocationTableName = DealerLocation::getTableName();

        $queryHardcodeFees = DealerLocationQuoteFee::select(
            DB::raw("NULL AS id,
                   NULL AS dealer_location_id,
                   'environmental_fee' AS fee_type,
                   'Environmental Fee' AS title,
                   NULL AS amount,
                   NULL AS is_state_taxed,
                   NULL AS is_county_taxed,
                   NULL AS is_local_taxed,
                   NULL AS visibility,
                   NULL AS accounting_class")
        )->limit(1);

        // to prevent the union fail when it has been added some column to `dealer_location_quote_fee` table
        $columns = DB::raw(
            "{$quoteFeeTableName}.id, {$quoteFeeTableName}.dealer_location_id, fee_type, title, amount," .
            ' is_state_taxed, is_county_taxed, is_local_taxed, visibility, accounting_class'
        );

        $query = DealerLocationQuoteFee::areVisible()
            ->select($columns)
            ->where('fee_type', '!=', '')
            ->union($queryHardcodeFees);

        if (isset($params['dealer_id'])) {
            $query->join(
                $dealerLocationTableName,
                $dealerLocationTableName . '.dealer_location_id',
                '=',
                $quoteFeeTableName . '.dealer_location_id'
            );
            $query->where($dealerLocationTableName . '.dealer_id', '=', $params['dealer_id']);
        }

        if (isset($params['dealer_location_id'])) {
            $query->where('dealer_location_id', '=', $params['dealer_location_id']);
        } else {
            // if there is not a provided dealer_location_id, it will group by fee_type
            $query->groupBy('fee_type');
        }

        if (isset($params['visibility'])) {
            $query->whereIn('visibility', $params['visibility']);
        }

        if (isset($params['search_term'])) {
            $query->where(function ($query) use ($params): void {
                $query->where('fee_type', 'LIKE', '%' . $params['search_term'] . '%');
                $query->where('title', 'LIKE', '%' . $params['search_term'] . '%');
            });
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 1000; // seems not paginated
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * @param int $id
     * @param array $extraParams
     * @return LengthAwarePaginator
     */
    public function getByDealerLocationId(int $id, array $extraParams = []): LengthAwarePaginator
    {
        return $this->getAll(array_merge($extraParams, ['dealer_location_id' => $id]));
    }

    public function create(array $params): DealerLocationQuoteFee
    {
        $item = new DealerLocationQuoteFee();
        $item->fill($params)->save();

        return $item;
    }

    public function deleteByDealerLocationId(int $dealerLocationId): int
    {
        return DealerLocationQuoteFee::where(['dealer_location_id' => $dealerLocationId])->delete();
    }
}
