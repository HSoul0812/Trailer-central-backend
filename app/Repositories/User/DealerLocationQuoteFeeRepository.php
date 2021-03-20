<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User\DealerLocation;
use App\Models\User\DealerLocationQuoteFee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DealerLocationQuoteFeeRepository implements DealerLocationQuoteFeeRepositoryInterface
{
    public function getAll(array $params): LengthAwarePaginator
    {
        $quoteFeeTableName = DealerLocationQuoteFee::getTableName();
        $dealerLocationTableName = DealerLocation::getTableName();

        $queryHardcodeFees = DealerLocationQuoteFee::select(
            DB::raw("NULL AS id,
                   NULL AS dealer_location_id,
                   'environmental_fee' AS fee_type,
                   NULL AS amount,
                   NULL AS is_state_taxed,
                   NULL AS is_county_taxed,
                   NULL AS is_local_taxed,
                   NULL AS visibility,
                   NULL AS accounting_class")
        )->limit(1);

        $query = DealerLocationQuoteFee::areVisible()
            ->select($quoteFeeTableName . '.*')
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
        }

        if (isset($params['search_term'])) {
            $query->where(function ($query) use ($params): void {
                $query->where('fee_type', 'LIKE', '%' . $params['search_term'] . '%');
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
}
