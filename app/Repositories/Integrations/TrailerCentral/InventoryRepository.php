<?php

declare(strict_types=1);

namespace App\Repositories\Integrations\TrailerCentral;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryRepository implements InventoryRepositoryInterface
{
    private const SELECT_EXCEPT_COLUMNS = ['geolocation'];

    public function queryAllSince(?string $lastDateSynchronized): Builder
    {
        $query = DB::connection('mysql')
            ->table('inventory', 'i')
            ->select($this->getSerializableColumnsNames());

        if ($lastDateSynchronized) {
            $query->where('i.updated_at_auto', '>=', $lastDateSynchronized);
        }

        return $query->orderBy('i.updated_at_auto');
    }

    public function hideExpiredItems(Carbon $from, Carbon $to)
    {
        DB::connection('mysql')
            ->table('inventory')
            ->whereBetween('tt_payment_expiration_date', [$from, $to])
            ->update(['show_on_website' => 0]);
    }

    public function getSerializableColumnsNames(): array
    {
        return collect(Schema::connection('mysql')
            ->getColumnListing('inventory'))
            ->filter(fn (string $columnName): bool => !in_array($columnName, self::SELECT_EXCEPT_COLUMNS))
            ->toArray();
    }
}
